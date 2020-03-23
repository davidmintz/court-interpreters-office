<?php /** module/Admin/src/Service/DefendantNameService.php */

declare(strict_types=1);

namespace InterpretersOffice\Admin\Service;

use InterpretersOffice\Entity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * insert|update|etc defendant names
 */
class DefendantNameService
{
    
    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    const EXACT_DUPLICATE = 'exact';
    const INEXACT_DUPLICATE = 'inexact';
    const UPDATE_GLOBAL = 'global';
    const UPDATE_CONTEXTUAL = 'contextual';
    const USE_EXISTING_DUPLICATE = 'use_existing';
    const UPDATE_EXISTING_DUPLICATE = 'update_existing';

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Handles defendant-name updates.
     * 
     * This can get complicated. When they edit a name-entity, the entity as revised might 
     * collide with an existing one, and if so, the duplicate may be exact, or it may differ 
     * in capitalization or diactriticals. In the latter case, we have to ask them which they
     * prefer:  use the existing entity as is, or update it. 
     * 
     * On top of that, the names themselves do not represent distinct people, but rather 
     * more like attributes of Event entities. Therefore, if a name is associated with events 
     * bearing more than one docket number, we need to know whether the update is being applied 
     * to just one or more of those docket-contexts, or globally.
     * 
     * @param Entity\Defendant $entity
     * @param array $data the entity data
     * @param array $options extra data.  possible keys: 'event_id'...?
     */
    public function update(Entity\Defendant $entity, array $data, array $options = [] ) : array
    {
        $debug = [];
        $modified = 0;
        foreach (['given_names','surnames'] as $prop) {
            if ($entity[$prop] != $data[$prop]) {
                $entity[$prop] = $data[$prop];
                $modified++;
            }
        }
        if (! $modified) {
            return ['modified'=>false,'status'=>'success'];
        }
        // the first questions is whether there is a duplicate
        $duplicate = $this->findDuplicate($entity);
        $debug[] = "duplicate? ".($duplicate ? "yes":"no");
        $match = null;
        // and if so, whether it is exact or inexact
        if ($duplicate) {
            if ($this->isExactMatch($entity,$duplicate)) {
                $match = self::EXACT_DUPLICATE;    
            } else {
                $match = self::INEXACT_DUPLICATE;
            }
            $debug[] = "match: '$match'" ;
        }
       
        // and if it is inexact, whether they submitted a resolution policy:
        // use the existing one as is, or update the existing one        
        if ($match == self::INEXACT_DUPLICATE && ! ($data['duplicate_resolution']))
        {
            return [
                'status' => 'aborted',
                'inexact_duplicate_found' => true,
                'existing_entity' => $duplicate->toArray(),
            ];
        }        
        // now we need to know if the update is contextual or global
        $contexts_submitted = isset($data['contexts']) ? 
            array_map(function($i){ return json_decode($i, true);},$data['contexts']) :[];

        $all_contexts =  $this->em->getRepository(Entity\Defendant::class)
            ->findDocketAndJudges($entity->getId());
        
        $update_type =  $all_contexts == $contexts_submitted ? self::UPDATE_GLOBAL : self::UPDATE_CONTEXTUAL;
        $debug[] = 'type of update: '.$update_type;
        $result = [];
        $entity_to_delete = null;

        $db = $this->em->getConnection();
        $db->beginTransaction();
        switch ($match) {
            case null:
                // easiest case
                if ($update_type == self::UPDATE_GLOBAL) {
                    $debug[] = "no duplicate, doing global update";
                    $update = 'UPDATE defendant_names SET surnames = ?, given_names = ? WHERE id = ?';
                    $params = [$data['surnames'],$data['given_names'],$entity->getId()];
                    $result['deft_name_updated'] = $db->executeUpdate($update,$params);
                    $id = $entity->getId();                    
                } else { 
                    // we have to insert a new name, then update defendants_events as appropriate
                    $debug[] = "DUDE! no duplicate, CONTEXTUAL update";                    
                    // $this->em->transactional(function($em) use ($data) { ...})              
                    // nope... duplicate entry error. don't ask me why.
                    $result['deft_name_inserted'] = $db->executeUpdate('INSERT INTO defendant_names (given_names,surnames)
                            VALUES (?,?)',[$data['given_names'],$data['surnames']]
                    );
                    $id = $db->lastInsertId();
                    // $result['deft_events_updated'] = $this->doDeftEventsUpdate((int)$id, $entity->getId(), $contexts_submitted);
                    $result = array_merge($result, $this->doRelatedTableUpdates((int)$id, $entity->getId(), $contexts_submitted));
                }
            break;

            case self::EXACT_DUPLICATE:
                
                $id = (int)$duplicate->getId();
                if ($update_type == self::UPDATE_GLOBAL) {
                    // this is the case where there may be an orphan to remove after we're done
                    $debug[] = "EXACT duplicate, global update";
                    // $result['deft_events_updated'] = $this->doDeftEventsUpdate($id,$entity->getId());
                    $result = array_merge($result, $this->doRelatedTableUpdates((int)$id, $entity->getId(), $contexts_submitted));
                    $entity_to_delete = $entity;
                } else {
                    $debug[] = "EXACT duplicate, contextual update, DUDE!";                    
                    // $event_ids = $this->getEventIdsForContexts($contexts_submitted,$entity);                    
                    // $result['deft_events_updated'] = $this->doDeftEventsUpdate($id, $entity->getId(), $contexts_submitted);
                    $result = array_merge($result, $this->doRelatedTableUpdates((int)$id, $entity->getId(), $contexts_submitted));
                    /** @todo again, consider defendants_requests and orphan removal */
                }                                                     
            break;

            case self::INEXACT_DUPLICATE;
                $id = (int)$duplicate->getId();
                if ($update_type == self::UPDATE_GLOBAL) {
                    $debug[] = "INEXACT duplicate, global update; duplicate resolution: " .$data['duplicate_resolution'];
                    if ($data['duplicate_resolution'] == self::UPDATE_EXISTING_DUPLICATE) {
                        $update = 'UPDATE defendant_names SET surnames = ?, given_names = ? WHERE id = ?';
                        $params = [$data['surnames'],$data['given_names'],$id];
                        $result['deft_name_updated'] = $db->executeUpdate($update,$params);
                        // since it's global, no defendants_events update is required
                        $entity_to_delete = $entity;
                    } else { 
                        // use the existing name in the provided contexts                        
                        // $result['deft_events_updated'] = $this->doDeftEventsUpdate($id,$entity->getId(),$contexts_submitted);
                        $result = array_merge($result, $this->doRelatedTableUpdates((int)$id, $entity->getId(), $contexts_submitted));                       
                        /** @todo consider defendants_requests */
                    }
                } else { // contextual update
                    $debug[] = "INEXACT duplicate, contextual update; duplicate resolution: " .$data['duplicate_resolution'];
                    if ($data['duplicate_resolution'] == self::UPDATE_EXISTING_DUPLICATE) {
                        // ...first update the name
                        $update = 'UPDATE defendant_names SET surnames = ?, given_names = ? WHERE id = ?';
                        $params = [$data['surnames'],$data['given_names'],$duplicate->getId()];
                        $result['deft_name_updated'] = $db->executeUpdate($update,$params);
                    }
                    // and now use the duplicate to update defendants_events                    
                    //$event_ids = $this->getEventIdsForContexts($contexts_submitted,$entity); 
                    // $result['deft_events_updated'] =  $this->doDeftEventsUpdate($duplicate->getId(),$entity->getId(),$contexts_submitted);
                    $result = array_merge($result, $this->doRelatedTableUpdates($id, $entity->getId(), $contexts_submitted));
                    $result['entity'] = ['given_names'=>$data['given_names'],'surnames'=>$data['surnames'],'id'=>$id];                  
                }
            break;
        }
        // works fine with MySQL, but not Sqlite
        // $purge = 'DELETE d FROM defendant_names d LEFT JOIN defendants_events de ON d.id = de.defendant_id 
        // LEFT JOIN defendants_requests dr ON d.id = dr.defendant_id WHERE de.defendant_id IS NULL AND dr.defendant_id IS NULL';
        // $result['orphaned_deftnames_deleted'] = $db->executeUpdate($purge);
        if ($entity_to_delete) {
            $result['orphaned_deftnames_deleted'] =  $db->executeUpdate('DELETE FROM defendant_names WHERE id = ?',[$entity->getId()]);
        }
        $db->commit();
        $this->em->getRepository(Entity\Defendant::class)->deleteCache();                
        $result['status'] = 'success';
        $result['entity'] = ['given_names'=>$data['given_names'],'surnames'=>$data['surnames'],'id'=>$id];
        $result['debug'] = $debug;

        return $result;

    }

    /**
     * runs update query on defendants_events
     * 
     * @param int $old_id
     * @param int $new_id
     * @param array $contexts
     * @return int rows affected
     */
    public function doDeftEventsUpdate(int $old_id, int $new_id, array $contexts = []) : int
    {
        $db = $this->em->getConnection();
        $sql = 'UPDATE defendants_events SET defendant_id = ? WHERE defendant_id = ?';
        $params = [$old_id, $new_id];
        $types = null;
        if ($contexts) {
            $sql .= ' AND event_id IN (?)';
            $in = $this->getEventIdsForContexts($contexts, $new_id);
            $params[] = $in;
            $types = [null, null, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY];
        }

        return $db->executeUpdate($sql,$params,$types);
    }

    /**
     * runs update query on defendants_requests
     * 
     * @param int $old_id
     * @param int $new_id
     * @param array $contexts
     * @return int rows affected
     */
    public function doDeftRequestsUpdate(int $old_id, $new_id, array $contexts = []) : int
    {
        $db = $this->em->getConnection();
        $sql = 'UPDATE defendants_requests SET defendant_id = ? WHERE defendant_id = ?';
        $params = [$old_id, $new_id];
        $types = null;
        if ($contexts) {
            $sql .= ' AND request_id IN (?)';
            $in = $this->getRequestIdsForContexts($contexts, $new_id);
            $params[] = $in;
            $types = [null, null, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY];
        }

        return $db->executeUpdate($sql,$params,$types);

    }

    /**
     * updates both defendants_events and defendants_requests
     * 
     * @param int $old_id
     * @param int $new_id
     * @param array $contexts
     * @return array
     */
    public function doRelatedTableUpdates(int $old_id, $new_id, array $contexts = []) : array
    {
        $deft_events_updated = $this->doDeftEventsUpdate($old_id,$new_id,$contexts);
        $deft_requests_updated = $this->doDeftRequestsUpdate($old_id,$new_id,$contexts);

        return compact('deft_events_updated','deft_requests_updated');
    }

    /**
     * returns Event entity for docket-judge contexts
     * 
     * @param array $contexts
     * @param Entity\Defendant $defendant defendant name
     * 
     * @return array
     */
    public function getEventIdsForContexts(array $contexts, int $id): array//Entity\Defendant $defendant) : array
    {
        $db = $this->em->getConnection();
        $qb = $db->createQueryBuilder();
        $qb->select('e.id')->distinct()->from('events','e')
            ->join('e', 'defendants_events','de','e.id = de.event_id')
            ->where('de.defendant_id = '.$qb->createNamedParameter($id));   //($defendant->getId()));
        $sql = $this->composeAndWhere($qb,$contexts,'e');        
        $qb->andWhere($sql);
        $result = $db->executeQuery($qb->getSql(),$qb->getParameters());
        
        return $result->fetchAll(\PDO::FETCH_COLUMN);
        
    }

    /**
     * helper for assembling SQL clause
     * 
     * @param QueryBuilder $qb
     * @param array $contexts
     * @param string $alias
     * @return string
     */
    public function composeAndWhere(QueryBuilder $qb, array $contexts, string $alias) : string
    {
        foreach ($contexts as $context) {           
            $condition1 = "{$alias}.docket = ". $qb->createNamedParameter($context['docket']);
            if ($context['judge_id']) {
                $condition2 = "{$alias}.judge_id = ".$qb->createNamedParameter($context['judge_id']);
            } else {
                $condition2 = "{$alias}.anonymous_judge_id = ".$qb->createNamedParameter($context['anon_judge_id']);
            }
            $or[] = "($condition1 AND $condition2)";
        }
        return implode(' OR ',$or);
    }

    /**
     * returns Request entity ids for docket-judge contexts
     * 
     * @param array $contexts
     * @param int $id
     * 
     * @return array
     */
    public function getRequestIdsForContexts(array $contexts, int $id) : array
    {
        $db = $this->em->getConnection();
        $qb = $db->createQueryBuilder();
        $qb->select('r.id')->distinct()->from('requests','r')
            ->join('r', 'defendants_requests','dr','r.id = dr.request_id')
            ->where('dr.defendant_id = '.$qb->createNamedParameter($id));
        $sql = $this->composeAndWhere($qb,$contexts,'r');        
        $qb->andWhere($sql);
        $result = $db->executeQuery($qb->getSql(),$qb->getParameters());
        
        return $result->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * attempts to insert a new defendant name 
     * 
     * @param array $data
     * @return array
     */
    public function insert(Array $data) : Array    
    {
        $entity = new Entity\Defendant();
        $entity->setGivenNames($data['given_names'])
            ->setSurnames($data['surnames']);
        try {
            $this->em->persist($entity);
            $this->em->flush();
            return ['status'=>'success',
                'data'=>[
                    'id'=>$entity->getId(), 
                    'given_names' => $data['given_names'],
                    'surnames' => $data['surnames'],
                ]
            ];
        } catch (UniqueConstraintViolationException $e) {
            $existing_entity = $this->findDuplicate($entity);
            
            return [
                'status' => 'error',
                'duplicate_entry_error' => true,
                'exact_match' => $this->isExactMatch($entity, $existing_entity),
                'existing_entity' => [
                    'surnames' => $existing_entity->getSurnames(),
                    'given_names' => $existing_entity->getGivenNames(),
                    'id' => $existing_entity->getId(),
                ],
            ];            
        }        
    }

    /**
     * 
     * 
     * @param Entity\Defendant $a
     * @param Entity\Defendant $b
     * @return bool true if match is exact (binary, literal)
     */
    public function isExactMatch(Entity\Defendant $a, Entity\Defendant $b) : bool
    {
        return $a->getGivenNames() == $b->getGivenNames()
            && $a->getSurNames() == $b->getSurnames();
    }

    /**
     * find existing entity with same properties
     *
     * @param  Entity\Defendant $defendant
     * @return Defendant|null
     */
    public function findDuplicate(Entity\Defendant $defendant) :? Entity\Defendant
    {
        $found = $this->em->getRepository(Entity\Defendant::class)->findOneBy([
            'given_names'=>$defendant['given_names'],
            'surnames'=>$defendant['surnames']
        ]);
        if (! $found) {
            return null;
        }
        if ($defendant->getId() && $found->getId() == $defendant->getId()) {
            // same object
            return null;
        }

        return $found;
    }
}