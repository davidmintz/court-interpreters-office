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
     * attempts to update a defendant name
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
                'existing_entity' => [
                    'given_names' => $duplicate['given_names'],
                    'surnames'    => $duplicate['surnames'],
                 ],
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
        
        /**@todo consider defendants_requests as well. if the docket|judge are the same... */
        $db = $this->em->getConnection();
        $db->beginTransaction();
        switch ($match) {
            case null:
                // easiest case
                if ($update_type == self::UPDATE_GLOBAL) {
                    $debug[] = "no duplicate, doing global update";
                    $update = 'UPDATE defendant_names SET surnames = ?, given_names = ? WHERE id = ?';
                    $params = [$data['surnames'],$data['given_names'],$entity->getId()];
                    $deft_name_updated = $db->executeUpdate($update,$params);
                    $result =[
                        'status'=>'success',
                        'deft_name_updated' => $deft_name_updated,
                        'entity' => [
                            'id'=>$entity['id'],
                            'given_names'=>$entity['given_names'],
                            'surnames' => $entity['surnames']
                    ]];
                    
                } else { 
                    // we have to insert a new name, then update defendants_events as appropriate
                    $debug[] = "DUDE! no duplicate, contextual update";
                    $debug['context'] = $data['contexts'];
                    // $this->em->transactional(function($em) use ($data) { ...})              
                    // nope... duplicate entry error. don't ask me why.
                    $db->executeUpdate('INSERT INTO defendant_names (given_names,surnames)
                            VALUES (?,?)',[$data['given_names'],$data['surnames']]
                    );
                    $id = $db->lastInsertId();                    
                    $event_ids = $this->getEventIdsForContexts($contexts_submitted,$entity);                    
                    $result['deft_events_updated'] = $this->doDeftEventsUpdate((int)$id, $entity->getId(), $event_ids);
                }
            break;

            case self::EXACT_DUPLICATE:
                
                if ($update_type == self::UPDATE_GLOBAL) {
                    // this is the case where there may be an orphan to remove
                    // after we're done
                    $debug[] = "EXACT duplicate, global update";
                    $result['deft_events_updated'] = $this->doDeftEventsUpdate($duplicate->getId(),$entity->getId());
                    /** @todo again, consider defendants_requests and orphan removal */
                } else {
                    $debug[] = "EXACT duplicate, contextual update, DUDE!";                    
                    $event_ids = $this->getEventIdsForContexts($contexts_submitted,$entity);                    
                    $params = [$duplicate->getId(),$entity->getId(),$event_ids,];
                    $result['deft_events_updated'] = $this->doDeftEventsUpdate(
                        $duplicate->getId(),$entity->getId(),$event_ids
                    );                    
                    /** @todo again, consider defendants_requests and orphan removal */
                }
            break;

            case self::INEXACT_DUPLICATE;
                if ($update_type == self::UPDATE_GLOBAL) {
                    $debug[] = "INEXACT duplicate, global update; dup resolution: " .$data['duplicate_resolution'];
                    if ($data['duplicate_resolution'] == self::UPDATE_EXISTING_DUPLICATE) {
                        $update = 'UPDATE defendant_names SET surnames = ?, given_names = ? WHERE id = ?';
                        $params = [$data['surnames'],$data['given_names'],$duplicate->getId()];
                        $result['deft_name_updated'] = $db->executeUpdate($update,$params);
                        // since it's global, no defendants_events update is required
                        /** @todo again, consider defendants_requests and orphan removal */
                    } else { 
                        // use the existing name in the provided contexts
                        $event_ids = $this->getEventIdsForContexts($contexts_submitted,$entity); 
                        $result['deft_events_updated'] = $this->doDeftEventsUpdate($duplicate->getId(),$entity->getId(),$event_ids);                       
                        /** @todo consider defendants_requests */
                    }
                } else { // contextual update
                    $debug[] = "INEXACT duplicate, contextual update; dup resolution: " .$data['duplicate_resolution'];
                    if ($data['duplicate_resolution'] == self::UPDATE_EXISTING_DUPLICATE) {
                        // ...first update the name
                        $update = 'UPDATE defendant_names SET surnames = ?, given_names = ? WHERE id = ?';
                        $params = [$data['surnames'],$data['given_names'],$duplicate->getId()];
                        $result['deft_name_updated'] = $db->executeUpdate($update,$params);
                    }
                    // and now use the duplicate to update defendants_events                    
                    $event_ids = $this->getEventIdsForContexts($contexts_submitted,$entity); 
                    $result['deft_events_updated'] =  $this->doDeftEventsUpdate($duplicate->getId(),$entity->getId(),$event_ids);                  
                }
            break;
        }

        $db->commit();
        $this->em->getRepository(Entity\Defendant::class)->deleteCache();
        
        return [
            'debug' => $debug,
            'status' => 'WIP', 
            'result' => $result ?? "TBD",          
            'duplicate_resolution' => $data['duplicate_resolution'],
        ];

    }

    /**
     * runs update query on defendants_events
     * 
     * @param int $old_id
     * @param int $new_id
     * @param array event_ids
     * @return int rows affected
     */
    public function doDeftEventsUpdate(int $old_id, int $new_id, array $in = []) : int
    {
        $db = $this->em->getConnection();
        $sql = 'UPDATE defendants_events SET defendant_id = ? WHERE defendant_id = ?';
        $params = [$old_id, $new_id];
        $types = null;
        if ($in) {
            $sql .= ' AND event_id IN (?)';
            $params[] = $in;
            $types = [null, null, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY];
        }

        return $db->executeUpdate($sql,$params,$types);
    }

    /**
     * returns Event entity for docket-judge contexts
     * 
     * @param array $contexts
     * @param Entity\Defendant $defendant defendant name
     * 
     * @return array
     */
    public function getEventIdsForContexts(array $contexts, Entity\Defendant $defendant) : array
    {
        $db = $this->em->getConnection();
        $qb = $db->createQueryBuilder();
        $qb->select('e.id')->distinct()->from('events','e')
            ->join('e', 'defendants_events','de','e.id = de.event_id')
            ->where('de.defendant_id = '.$qb->createNamedParameter($defendant->getId()));
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
        return implode($or, ' OR ');
    }

    /**
     * returns Request entity ids for docket-judge contexts
     * 
     * @param array $contexts
     * @param Entity\Defendant $defendant defendant name
     * 
     * @return array
     */
    public function getRequestIdsForContexts(array $contexts, Entity\Defendant $defendant) : array
    {
        $db = $this->em->getConnection();
        $qb = $db->createQueryBuilder();
        $qb->select('r.id')->distinct()->from('requests','r')
            ->join('r', 'defendants_requests','dr','r.id = dr.request_id')
            ->where('dr.defendant_id = '.$qb->createNamedParameter($defendant->getId()));
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