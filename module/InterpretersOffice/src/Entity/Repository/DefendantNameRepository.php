<?php

/** module/InterpretersOffice/src/Entity/DefendantNameRepository.php */

namespace InterpretersOffice\Entity\Repository;

use InterpretersOffice\Service\ProperNameParsingTrait;
use InterpretersOffice\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Cache\CacheProvider;

use Zend\Paginator\Paginator as ZendPaginator;
use Zend\Dom\Exception\RuntimeException;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use InterpretersOffice\Admin\Form\DefendantForm;
use InterpretersOffice\Entity\DefendantName;
use InterpretersOffice\Entity\DefendantEvent;

use Zend\Log\LoggerAwareInterface;
use Zend\Log\LoggerAwareTrait;

/**
 * custom EntityRepository class for the DefendantName entity.
 *
 */
class DefendantNameRepository extends EntityRepository implements CacheDeletionInterface
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
                . ' AS label FROM  InterpretersOffice\Entity\DefendantName d '
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
             $parameters['non_hyphenated'] = $non_hypthenated;
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
     * @return ZendPaginator
     */
    public function paginate($search_term, $page = 1)
    {
        $dql = 'SELECT d FROM InterpretersOffice\Entity\DefendantName d WHERE ';
        $name = $this->parseName($search_term);
        $parameters = ['surnames' => "$name[last]%"];
        $dql .= $this->getDqlWhereClause($name, $parameters);
        $dql   .= "ORDER BY d.surnames, d.given_names";
        $query = $this->createQuery($dql)
                ->setParameters($parameters)
                ->setMaxResults(20);
        $adapter = new DoctrineAdapter(new ORMPaginator($query));
        $paginator = new ZendPaginator($adapter);

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
            JOIN e.defendantsEvents de JOIN de.defendant d LEFT JOIN e.judge j
            LEFT JOIN e.anonymousJudge aj
            WHERE d.id = :id GROUP BY e.docket,aj.id,j.id
            ORDER BY e.docket, judge';

        return $this->createQuery($dql)->setParameters(['id' => $id])
            ->getResult();
    }
    /**
     * find existing entity with same properties except id
     *
     * @param  EntityDefendantName $defendantName
     * @return DefendantName|null
     */
    public function findDuplicate(Entity\DefendantName $defendantName)
    {
        $dql = 'SELECT d FROM InterpretersOffice\Entity\DefendantName d
        WHERE d.given_names = :given_names
        AND d.surnames = :surnames ';//AND d.id <> :id';

        return $this->createQuery($dql)->setParameters([
            'given_names' => $defendantName->getGivenNames(),
            'surnames' => $defendantName->getSurnames(),
            //'id' => $defendantName->getId()
        ])->getOneOrNullResult();
    }

    /**
     * gets DefendantEvents for DefendantName
     *
     * interesting fact: INDEX BY does not trigger an error but neither
     * does it seem to work unless the other columns are scalar
     *
     * @param  EntityDefendantName $defendantName
     * @param  int $exclude_event_id event to exclude from query
     * @return Array
     */
    public function getDefendantEventsForDefendant(
        Entity\DefendantName $defendantName,
        $exclude_event_id = null
    ) {
        $dql = 'SELECT de FROM InterpretersOffice\Entity\DefendantEvent de
            JOIN de.defendant d JOIN de.event e WHERE d.id = :id';
        $params = ['id' => $defendantName->getId()];
        if ($exclude_event_id) {
            $dql .= ' AND e.id <> :event_id';
            $params['event_id'] = $exclude_event_id;
        }
        return   $this->createQuery($dql)
            ->setParameters($params)
            ->getResult();
    }

    /**
     * updates DefendantName and DefendantEvent entities
     *
     * still a work in progress
     *
     * @param  Entity\DefendantName $defendantName
     * @param  Array $occurrences array of JSON strings
     * @param  Entity\DefendantName $existing_name
     * @param  string $duplicate_resolution whether to update or use existing
     * @param  int $event_id id of event related to $defendantName
     * @return Array result
     *
     * @todo when an orphaned name is dropped and swapped for an existing
     * one, return the id of the existing to the controller for the controller
     * to send back to update the view
     */
    public function updateDefendantEvents(
        Entity\DefendantName $defendantName,
        array $occurrences,
        Entity\DefendantName $existing_name = null,
        $duplicate_resolution = null,
        $event_id = null
    ) {
        $logger = $this->getLogger(); // temporary, perhaps
        $em = $this->getEntityManager();

        /** is it a global update, or an update of only a subset? */
        $logger->debug("event id is: " . ($event_id ?: "null"));

        foreach ($occurrences as $i => $occurrence) {
            // unpack submitted JSON strings schedule
            $occurrences[$i] = json_decode($occurrence, JSON_OBJECT_AS_ARRAY);
        }
        // get all the contexts (occurences) from database
        $all_occurrences = $this->findDocketAndJudges($defendantName);
        // if what's in the database == what was submitted, it's a global update
        $GLOBAL_UPDATE = ($all_occurrences == $occurrences);
        //$logger->debug("\$all_occurrences looks like: ".print_r($all_occurrences,true));
        //$logger->debug("\$occurrences looks like: ".print_r($occurrences,true));
        $GLOBAL_OR_PARTIAL = $GLOBAL_UPDATE ? 'global' : 'partial';

        // is there a matching name already existing?
        if (! $existing_name) {
            $MATCH = false;
        } elseif ($defendantName->getId() == $existing_name->getId()) {
            $MATCH = 'identical';
        } else {
            // if there's a match, is it literal or inexact?
            $MATCH = $defendantName->equals($existing_name) ? 'literal'
                : 'inexact';
        }
        // if there is an inexact match, and no $duplicate_resolution
        //  strategy, return.
        if ('inexact' == $MATCH && ! $duplicate_resolution) {
            return [
                'inexact_duplicate_found' => 1,
                'status' => 'aborted',
                'debug' => 'required duplicate resolution not provided',
                'existing_entity' => (string)$existing_name,
                'update_type' => $GLOBAL_OR_PARTIAL
            ];
        }
        $logger->debug(sprintf(
            'in %s at %d match is %s, update is %s',
            __CLASS__,
            __LINE__,
            $MATCH ?: 'false',
            $GLOBAL_OR_PARTIAL
        ));
        $result = [ 'match' => $MATCH,'update_type' => $GLOBAL_OR_PARTIAL, 'events_affected' => [] ];
        if ($GLOBAL_UPDATE) {
            switch ($MATCH) {
                case false:
                case 'identical':
                    try {
                        $logger->debug("flushing out global update");
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
                        ->setGivenNames($defendantName->getGivenNames())
                        ->setSurnames($defendantName->getSurnames());
                        $logger->debug("we updated the existing name");
                        $result['updated_deftname'] = $existing_name->getId();
                    }
                // swap out $deftName for existing, and detach
                    $deft_events = $this->getDefendantEventsForDefendant($defendantName, $event_id);
                    $result['count_deft_events_updated'] = count($deft_events);
                    foreach ($deft_events as $de) {
                        $de->setDefendantName($existing_name);
                        $result['events_affected'][] = $de->getEvent()->getId();
                    }
                    $logger->debug(sprintf("is there a childless name to remove? (at %d)", __LINE__));
                    if (! $this->hasRelatedEntities($defendantName->getId())) {
                        $logger->debug("we think so");
                        $result['deftname_deleted'] = $defendantName->getId();
                        $em->remove($defendantName);
                    } else {
                        $logger->debug("we think not.");
                        $em->detach($defendantName);
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
                    'deft_events_updated' => count($deft_events),
                ]);
            } catch (\Exception $e) {
                return array_merge($result, [
                    'status' => 'error',
                    'exception_class' => get_class($e),
                    'message' => $e->getMessage(),
                ]);
            }
        } else { // PARTIAL update.
            $deft_events = $this
                ->getDeftEventsForOccurrences($occurrences, $defendantName);
            $logger->debug(sprintf(
                'at line %d: existing is %s, submitted is now %s; '
                .'%d occurrences, %d deft events found; ',
                __LINE__,
                $existing_name,
                $defendantName,
                count($occurrences),
                count($deft_events)
            ));
            switch ($MATCH) {
                case 'identical':
                    $logger->debug('submitted entity is identical with entity found at '.__LINE__);
                    break; // simple flush() should do it
                case false:
                // a new name has to be inserted; this one has to be detached
                    $new = (new Entity\DefendantName)
                    ->setGivenNames($defendantName->getGivenNames())
                    ->setSurnames($defendantName->getSurnames());
                    $em->persist($new);
                    $em->detach($defendantName);
                    foreach ($deft_events as $de) {
                        $de->setDefendantName($new);
                        $result['events_affected'][] = $de->getEvent()->getId();
                    }
                    $logger->debug('no existing match, we created new defendant name at line '.__LINE__);
                    break;

                case 'inexact':
                    if ($duplicate_resolution == DefendantForm::UPDATE_EXISTING) {
                        $existing_name
                        ->setGivenNames($defendantName->getGivenNames())
                        ->setSurnames($defendantName->getSurnames());
                    }
                // don't break
                case 'literal':
                    foreach ($deft_events as $de) {
                        $de->setDefendantName($existing_name);
                    }
                    if (! $defendantName->hasRelatedEntities()) {
                        $logger->debug("no related entities for $defendantName at ".__LINE__);
                        $em->remove($defendantName);
                    } else {
                        $logger->debug("yes related entities for $defendantName, gonna detach() at ".__LINE__);
                        $em->detach($defendantName);
                    }
                    $result['deftname_replaced_by'] = $existing_name->getId();
                    break;
            }

            try {
                $em->flush();
                $return = array_merge($result, [
                    'status' => 'success',
                    'deft_events_updated' => count($deft_events),
                ]);
                if (isset($new)) {
                    $return['insert_id'] = $new->getId();
                }
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
     * gets all DefendantEvent entities for given set of docket/judge contexts
     *
     * @param  Array $occurrences
     * @param  EntityDefendantName $defendantName
     * @return Entity\DefendantName[]
     */
    private function getDeftEventsForOccurrences(
        array $occurrences,
        Entity\DefendantName $defendantName
    ) {
        $dql = 'SELECT de FROM InterpretersOffice\Entity\DefendantEvent de
        JOIN de.defendant d JOIN de.event e
        LEFT JOIN e.anonymousJudge aj LEFT JOIN e.judge j
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
        //$this->getLogger()->debug("DQL: $dql\nparams:\n"
        // . print_r(['id'=>$defendantName->getId()],true));
        return $this->createQuery($dql)->useResultCache(false)
            ->setParameters(['id' => $defendantName->getId()])
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
        $dql = 'SELECT COUNT(e.id) FROM InterpretersOffice\Entity\DefendantName
            d  JOIN d.events e  WHERE d.id = :id';
        return $this->getEntityManager()->createQuery($dql)->setParameters([
            'id' => $id
        ])->getSingleScalarResult() ? true : false;
    }
}
