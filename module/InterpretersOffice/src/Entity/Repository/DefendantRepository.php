<?php

/** module/InterpretersOffice/src/Entity/DefendantRepository.php */

namespace InterpretersOffice\Entity\Repository;

use InterpretersOffice\Service\ProperNameParsingTrait;
use InterpretersOffice\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Cache\CacheProvider;

use Laminas\Paginator\Paginator as LaminasPaginator;
use Laminas\Dom\Exception\RuntimeException;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use InterpretersOffice\Admin\Form\DefendantForm;
use InterpretersOffice\Entity\Defendant;


use Laminas\Log\LoggerAwareInterface;
use Laminas\Log\LoggerAwareTrait;

use PDO;

/**
 * custom EntityRepository class for the Defendant entity.
 *
 */
class DefendantRepository extends EntityRepository implements CacheDeletionInterface
{
    use ResultCachingQueryTrait;

    use ProperNameParsingTrait;
    use LoggerAwareTrait;

    /**
     * cache
     *
     * @var CacheProvider
     */
    protected $cache;

    /**
     * constructor
     *
     * @param \Doctrine\ORM\EntityManager  $em    The EntityManager to use.
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class The class descriptor.
     */
    public function __construct($em, \Doctrine\ORM\Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->cache = $em->getConfiguration()->getResultCacheImpl();
        $this->cache->setNamespace('defendants');
    }

    /**
     * returns an array of names for defendant autocompletion
     *
     * @param string $term
     * @param int $limit
     * @return array
     */
    public function autocomplete($term, $limit = 20)
    {
        $name = $this->parseName($term);
        $parameters = ['surnames' => "$name[last]%"];

        $dql = "SELECT d.id AS value, CONCAT(d.surnames, ', ', d.given_names) "
                . ' AS label FROM  InterpretersOffice\Entity\Defendant d '
                . ' WHERE ';

        $dql .= $this->getDqlWhereClause($name, $parameters);
        $dql   .= "ORDER BY d.surnames, d.given_names";
        $query = $this->createQuery($dql)
                ->setParameters($parameters)
                ->setMaxResults($limit);

        return $query->getResult();
    }

    /**
     * gets WHERE clause for DQL query
     *
     * also modifies $parameters which are passed by reference
     *
     * @param  Array  $name       array
     * @param  Array  $parameters query parameters
     * @return string            DQL WHERE clause
     */
    protected function getDqlWhereClause(array $name, array &$parameters)
    {
        $dql = '';
        // we don't do hyphens
        if (! strstr($name['last'], '-')) {
            $dql .= 'd.surnames LIKE :surnames ';
        } else {
             $non_hypthenated = str_replace('-', ' ', $name['last']);
             $dql .= '(d.surnames LIKE :surnames OR d.surnames LIKE :non_hyphenated) ';
             $parameters['non_hyphenated'] = "$non_hypthenated%";
        }

        if ($name['first']) {
            $parameters['given_names'] = "$name[first]%";
            $dql .= 'AND d.given_names LIKE :given_names ';
        } else {
            // we don't like empty first names, so if there are any (legacy)
            // rows that are missing a first name, don't returning them
            $dql .= "AND d.given_names <> '' " ;
        }

        return $dql;
    }


    /**
     * returns defendant names wrapped in a paginator.
     *
     * @param string $search_term
     * @param int $page
     * @return LaminasPaginator
     */
    public function paginate($search_term, $page = 1)
    {
        $dql = 'SELECT d FROM InterpretersOffice\Entity\Defendant d WHERE ';
        $name = $this->parseName($search_term);
        $parameters = ['surnames' => "$name[last]%"];
        $dql .= $this->getDqlWhereClause($name, $parameters);
        $dql   .= "ORDER BY d.surnames, d.given_names";
        $query = $this->createQuery($dql)
                ->setParameters($parameters)
                ->setMaxResults(20);
        $adapter = new DoctrineAdapter(new ORMPaginator($query));
        $paginator = new LaminasPaginator($adapter);

        return $paginator->setCurrentPageNumber($page)->setItemCountPerPage(20);
    }

    /**
     * implements cache deletion
     *
     * @param int $cache_id optional cache id
     */
    public function deleteCache($cache_id = null)
    {
         $this->cache->setNamespace('defendants');
         $this->cache->deleteAll();
    }

    /**
     * finds occurences of defendant id by judge and docket number
     *
     * @param  int $id defendant id
     * @return array
     */
    public function findDocketAndJudges($id)
    {
        $dql = 'SELECT COUNT(e.id) events, e.docket,
            COALESCE(j.lastname, aj.name) judge, j.id judge_id,
            aj.id anon_judge_id
            FROM InterpretersOffice\Entity\Event e
            JOIN e.defendants d LEFT JOIN e.judge j
            LEFT JOIN e.anonymous_judge aj
            WHERE d.id = :id GROUP BY e.docket,aj.id,j.id
            ORDER BY e.docket, judge';

        return $this->createQuery($dql)->setParameters(['id' => $id])
            ->getResult();
    }

    /**
     * find existing entity with same properties except id
     *
     * @param  Entity\Defendant $defendant
     * @return Defendant|null
     */
    public function findDuplicate(Entity\Defendant $defendant)
    {
        $dql = 'SELECT d FROM InterpretersOffice\Entity\Defendant d
        WHERE d.given_names = :given_names
        AND d.surnames = :surnames ';

        return $this->createQuery($dql)->setParameters([
            'given_names' => $defendant->getGivenNames(),
            'surnames' => $defendant->getSurnames(),
            //'id' => $defendant->getId()
        ])->getOneOrNullResult();
    }

    /**
     * gets DefendantEvents for Defendant
     *
     * interesting fact: INDEX BY does not trigger an error but neither
     * does it seem to work unless the other columns are scalar
     *
     * @param  Entity\Defendant $defendant
     * @param  int $exclude_event_id event to exclude from query
     * @return Array
     */
    public function __getDefendantEventsForDefendant(
        Entity\Defendant $defendant,
        $exclude_event_id = null
    ) {
        $dql = 'SELECT de FROM InterpretersOffice\Entity\DefendantEvent de
            JOIN de.defendant d JOIN de.event e WHERE d.id = :id';
        $params = ['id' => $defendant->getId()];
        if ($exclude_event_id) {
            $dql .= ' AND e.id <> :event_id';
            $params['event_id'] = $exclude_event_id;
        }
        return   $this->createQuery($dql)
            ->setParameters($params)
            ->getResult();
    }
    private $result = [];
    /**
     * updates Defendant and DefendantEvent entities
     *
     * still a work in progress
     *
     * @param  Entity\Defendant $defendant
     * @param  Array $occurrences array of JSON strings
     * @param  Entity\Defendant $existing_name
     * @param  string $duplicate_resolution whether to update or use existing
     * @param  int $event_id id of event related to $defendant
     * @return Array result
     *
     * @todo when an orphaned name is dropped and swapped for an existing
     * one, return the id of the existing to the controller for the controller
     * to send back to update the view
     */
    public function updateDefendantEvents(
        Entity\Defendant $defendant,
        array $occurrences,
        Entity\Defendant $existing_name = null,
        $duplicate_resolution = null,
        $event_id = null
    ) {
        $logger = $this->getLogger(); // temporary, perhaps
        /** @var Doctrine\ORM\EntityManagerInterface $em */
        $em = $this->getEntityManager();

        $logger->debug("event id is: " . ($event_id ?: "null"));

        foreach ($occurrences as $i => $occurrence) {
            // unpack submitted JSON strings
            $occurrences[$i] = json_decode($occurrence, JSON_OBJECT_AS_ARRAY);
        }
        /** is it a global update, or an update of only a subset? */

        // get all the contexts (occurences) from database
        $all_occurrences = $this->findDocketAndJudges($defendant);
        // if what's in the database == what was submitted, it's a global update
        $GLOBAL_UPDATE = ($all_occurrences == $occurrences);
        $logger->debug("\$all_occurrences looks like: ".print_r($all_occurrences,true));
        //$logger->debug("\$occurrences looks like: ".print_r($occurrences,true));
        $GLOBAL_OR_PARTIAL = $GLOBAL_UPDATE ? 'global' : 'partial';

        // is there a matching name already existing?
        if (! $existing_name) {
            $MATCH = false;
        } elseif ($defendant->getId() == $existing_name->getId()) {
            // identical means same entity, same id
            $MATCH = 'identical';
        } else {
            // if there's a match, is it literal or inexact?
            $MATCH = $defendant->equals($existing_name) ? 'literal'
                : 'inexact';
        }
        // if there is an inexact match, and no $duplicate_resolution
        //  strategy, return.
        if ('inexact' == $MATCH && ! $duplicate_resolution) {
            return [
                'inexact_duplicate_found' => 1,
                'status' => 'aborted',
                'debug' => 'required duplicate resolution not provided',
                //'existing_entity' => (string)$existing_name,
                'existing_entity' => [
                    'given_names' => $existing_name->getGivenNames(),
                    'surnames'   => $existing_name->getSurnames(),
                ],
                'update_type' => $GLOBAL_OR_PARTIAL
            ];
        }
        $logger->debug(sprintf(
            'in %s at %d match is %s, update is %s',
            __CLASS__,  __LINE__,
            $MATCH ?: 'false',
            $GLOBAL_OR_PARTIAL
        ));
        $result = [ 'match' => $MATCH,'update_type' => $GLOBAL_OR_PARTIAL,
            'events_affected' => 0 ];
        if ($GLOBAL_UPDATE) {
            switch ($MATCH) {
                case false:
                case 'identical':
                    try {
                        $logger->debug("flushing out global update at ".__LINE__);
                        $em->flush();
                        return array_merge($result, [
                        'status' => 'success',
                        'debug' => 'no collision with existing match, global entity update.',
                        ]);
                    } catch (\Exception $e) {
                        return array_merge($result, [
                        'status' => 'error',
                        'exception_class' => get_class($e),
                        'message' => $e->getMessage(),
                        ]);
                    }
                    break;

                case 'inexact':
                case 'literal':
                    if ($duplicate_resolution == DefendantForm::UPDATE_EXISTING) {
                        $existing_name
                        ->setGivenNames($defendant->getGivenNames())
                        ->setSurnames($defendant->getSurnames());
                        $logger->debug("we updated the existing name");
                        $result['updated_deftname'] = $existing_name->getId();
                    }
                    $db = $this->getEntityManager()->getConnection();
                    $result['events_affected'] = $db->executeUpdate(
                        'UPDATE defendants_events SET defendant_id = :new WHERE defendant_id = :old',
                        [':new' => $existing_name->getId(), ':old' => $defendant->getId()]
                    );

                    $logger->debug(sprintf("is there a childless name to remove? (at %d)", __LINE__));
                    if (! $this->hasRelatedEntities($defendant->getId())) {
                        $logger->debug("we think so");
                        $result['deftname_deleted'] = $defendant->getId();
                        $em->remove($defendant);
                    } else {
                        // this has to mean there are defendants_requests to be dealt with...
                        // considering something like the following (not yet sure it works as intended):
                        $logger->debug("we think not. trying requests...");
                       // $dql = 'SELECT COUNT(';
                        // but since the update is "global"
                        // maybe update defendants_requests as well? unfortunately this is NOT working, possibly
                        // for reasons related to https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/transactions-and-concurrency.html#approach-2-explicitly
                        $dockets = array_unique(array_column($occurrences,'docket'));
                        $sql = 'UPDATE defendants_requests dr JOIN requests r ON dr.request_id = r.id SET defendant_id = :new 
                        WHERE defendant_id = :old AND r.docket IN (:dockets)';
                        $docket_str = sprintf("'%s'",implode("','",$dockets));                        
                        $params = [':dockets'=>$docket_str,':new' => $existing_name->getId(), ':old' => $defendant->getId()];
                        // $em->transactional(function($em) use ($sql,$params){
                        // });
                        $result['requests_affected'] = $db->executeUpdate($sql, $params);
                        // to be continued...
                        $logger->debug("did: $sql with params",$params);
                        // ask again 
                        if (! $this->hasRelatedEntities($defendant->getId())) {
                            $logger->debug("removing old deft at ".__LINE__);
                            $em->remove($defendant);
                        } else {
                            $logger->debug("NOT removing old deft at ".__LINE__);
                            $em->detach($defendant);
                        }
                    }
                    $result['deftname_replaced_by'] = $existing_name->getId();
                    break; // pro forma
            }
            try {
                $logger->debug("flushing $MATCH match at ". __LINE__);
                $em->flush();
                $return  = array_merge($result, [
                    'status' => 'success',
                    'debug' => "match was $MATCH",
                    //'deft_events_updated' => count($deft_events),
                ]);
            } catch (\Exception $e) {
                return array_merge($result, [
                    'status' => 'error',
                    'exception_class' => get_class($e),
                    'message' => $e->getMessage(),
                ]);
            }
        } else { // it's a PARTIAL update.
            $event_ids = $this
                ->getEventIdsForOccurrences($occurrences, $defendant);
            $logger->debug(sprintf(
                'at line %d: existing is %s, submitted is now %s; '
                .'%d occurrences, %d deft-events found; ',
                __LINE__,
                $existing_name,
                $defendant,
                count($occurrences),
                count($event_ids)
            ));
            switch ($MATCH) {
                case 'identical':
                    $logger->debug('submitted entity is identical with entity found at '.__LINE__);
                    break; // simple flush() should do it
                case false:
                    $em->detach($defendant);
                    /** @var Doctrine\DBAL\Connection $db */
                    $db = $em->getConnection();
                    $db->executeUpdate(
                        'INSERT INTO defendant_names (given_names,surnames)
                        VALUES (?,?)',
                        [$defendant->getGivenNames(),$defendant->getSurNames()]
                    );
                    $result['insert_id'] = $db->lastInsertId();
                    $sql = 'UPDATE defendants_events SET defendant_id = ?
                        WHERE defendant_id = ? AND event_id IN (?)';
                    $result['events_affected'] = $db->executeUpdate(
                        $sql,
                        [$result['insert_id'], $defendant->getId(),
                        array_column($event_ids, 'id')],
                        [
                            null, null, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY
                        ]
                    );
                    $logger->debug('no existing match, we created new defendant name at line '.__LINE__);
                    break;

                case 'inexact':
                    if ($duplicate_resolution == DefendantForm::UPDATE_EXISTING) {
                        $existing_name
                        ->setGivenNames($defendant->getGivenNames())
                        ->setSurnames($defendant->getSurnames());
                    }
                // no break
                case 'literal':
                    $sql = 'UPDATE defendants_events SET defendant_id = ?
                    WHERE defendant_id = ? AND event_id IN (?)';
                    $result['events_affected'] = $em->getConnection()
                        ->executeUpdate(
                        $sql,
                        [$existing_name->getId(), $defendant->getId(),
                        array_column($event_ids, 'id')],
                        [
                        null, null, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY
                        ]
                    );
                    $logger->debug("running partial update with literal match");
                    if (! $this->hasRelatedEntities($defendant->getId())) {
                        $logger->debug("no related entities for $defendant at ".__LINE__);
                        $em->remove($defendant);
                    } else {
                        $logger->debug("yes, related entities for $defendant, gonna detach() at ".__LINE__);
                        $em->detach($defendant);
                    }
                    $result['deftname_replaced_by'] = $existing_name->getId();
                    break;
            }

            try {
                $em->flush();
                $return = array_merge($result, [
                    'status' => 'success',
                    'deft_events_updated' => $result['events_affected'],
                ]);
            } catch (\Exception $e) {
                return array_merge($result, [
                    'status' => 'error',
                    'exception_class' => get_class($e),
                    'message' => $e->getMessage(). ' at '.__LINE__,
                ]);
            }
        }

        $this->logger->debug(sprintf("FYI: returning from %s at %d", __FUNCTION__, __LINE__));
        return $return;
    }

    /**
     * gets array of event_ids for given set of docket/judge contexts
     *
     * @param  Array $occurrences
     * @param  Entity\Defendant $defendant
     * @return array
     */
    private function getEventIdsForOccurrences(
        array $occurrences,
        Entity\Defendant $defendant
    ) {
        $dql = 'SELECT DISTINCT e.id
        FROM InterpretersOffice\Entity\Event e
        JOIN e.defendants d
        LEFT JOIN e.anonymous_judge aj LEFT JOIN e.judge j
        WHERE d.id = :id AND ';
        $where = [];
        foreach ($occurrences as $occurrence) {
            $where[]  = sprintf(
                "(e.docket = '{$occurrence['docket']}' AND %s.id = %d)",
                $occurrence['anon_judge_id'] ? 'aj' : 'j',
                $occurrence['anon_judge_id'] ?: $occurrence['judge_id']
            );
        }
        $string = implode(' OR ', $where);
        if (count($where) > 1) {
            $string = "($string)";
        }
        $dql .= $string;
        $this->getLogger()->debug("DQL: $dql\nparams:\n"
        . print_r(['id'=>$defendant->getId()],true));
        return $this->createQuery($dql)->useResultCache(false)
            ->setParameters(['id' => $defendant->getId()])
            ->getResult();
    }

    /**
     * whether defendant name $id has related entities
     *
     * @param int $id entity id
     * @return boolean true if related entities exist
     */
    public function hasRelatedEntities($id)
    {
        
        /* appears to work but it's too slow:
        "SELECT COUNT(e.id) FROM InterpretersOffice\Entity\Event e  
        JOIN e.defendants d  
        LEFT JOIN InterpretersOffice\Requests\Entity\Request r 
        WITH r.event = e LEFT JOIN r.defendants rd 
        WHERE d.id = 22668 OR rd.id = 22668"
        */
        /* this works fast in native SQL 
        
        SELECT (SELECT COUNT(e.id) FROM events e 
        JOIN defendants_events de ON e.id = de.event_id 
        WHERE de.defendant_id = 22668) + (SELECT COUNT(r.id) 
        FROM requests r  JOIN defendants_requests dr 
        ON r.id = dr.request_id WHERE dr.defendant_id = 22668) AS total;
        */
        /** @var \PDO $pdo */
        $pdo = $this->getEntityManager()->getConnection();
        $sql = 'SELECT (SELECT COUNT(e.id) FROM events e 
        JOIN defendants_events de ON e.id = de.event_id 
        WHERE de.defendant_id = :id) + (SELECT COUNT(r.id) 
        FROM requests r  JOIN defendants_requests dr 
        ON r.id = dr.request_id WHERE dr.defendant_id = :id) AS total';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id'=>$id]);
        $count = $stmt->fetch(\PDO::FETCH_COLUMN);
        $this->getLogger()->debug(__FUNCTION__.":  number of related entities is: $count");
        return $count ? true : false;
        // $dql = 'SELECT COUNT(e.id) FROM InterpretersOffice\Entity\Event
        //     e  JOIN e.defendants d  WHERE d.id = :id';
        // return $this->getEntityManager()->createQuery($dql)->setParameters([
        //     'id' => $id
        // ])->getSingleScalarResult() ? true : false;
    }
}
