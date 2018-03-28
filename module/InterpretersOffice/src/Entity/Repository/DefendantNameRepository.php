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
/**
 * custom EntityRepository class for the DefendantName entity.
 *
 *
 */
class DefendantNameRepository extends EntityRepository implements CacheDeletionInterface
{
    use ResultCachingQueryTrait;

    use ProperNameParsingTrait;

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

        $dql = "SELECT d.id AS value, CONCAT(d.surnames, ',  ',d.given_names) "
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
            JOIN e.defendantNames d LEFT JOIN e.judge j
            LEFT JOIN e.anonymousJudge aj
            WHERE d.id = :id GROUP BY e.docket,aj.id,j.id
            ORDER BY e.docket, judge';

        return $this->createQuery($dql)->setParameters(['id'=>$id])
            ->getResult();
    }

    public function findDuplicate(Entity\DefendantName $defendantName)
    {
        $dql = 'SELECT d FROM InterpretersOffice\Entity\DefendantName d
        WHERE d.given_names = :given_names
        AND d.surnames = :surnames AND d.id <> :id';

        return $this->createQuery($dql)->setParameters([
            'given_names' => $defendantName->getGivenNames(),
            'surnames' => $defendantName->getSurnames(),
            'id' => $defendantName->getId()
        ])->getOneOrNullResult();
    }
    public function getDefendantEventsForDefendant(Entity\DefendantName $defendantName)
    {
        $dql = 'SELECT  de FROM InterpretersOffice\Entity\DefendantEvent
            de JOIN de.defendant d WHERE d.id = :id';

        return  $this->createQuery($dql)
            ->setParameters(['id'=>$defendantName->getId()])
            ->getResult();
    }

    public function updateDefendantEvents(Entity\DefendantName $defendantName,
        Array $occurrences,Entity\DefendantName $existing_name  = null,
            $duplicate_resolution = null)
    {
        $number_of_contexts = count($occurrences);
        $em = $this->getEntityManager();
        $debug = sprintf("existing name: %s, occurrences: %d; ",
            $existing_name ? (string)$existing_name : '<none>',
            $number_of_contexts
        );
        if ($existing_name) {
            $literal_match = $existing_name->equals($defendantName);
            $debug .= sprintf('literal match? %s; ',$literal_match?"yes":"no");
        } else {
            $literal_match = null;
        }
        $all_occurrences = $this->findDocketAndJudges($defendantName->getId());
        $global_update = $all_occurrences == $occurrences;
        $debug .= sprintf("global update? %s; ",$global_update ? "yes":"no");
        // scenario:  name occurs in one or zero contexts, and there is
        // no collision expected with an existing name
        if ($number_of_contexts < 2 && ! $existing_name) {
            // nothing more to do, just update globally
            $em->flush();
            return ['status'=>'success','debug'=>$debug];
        }
        if ($global_update) {
            // they are updating the name in all contexts
            if ($literal_match) {
                // and there is a literal match already existing
                $debug .= "there is a literal match, and global update; ";
                $shit = $this->getDefendantEventsForDefendant($defendantName);
                $debug .= sprintf("found %d deft-events; ",count($shit));
                foreach($shit as $deft_event) {
                    $deft_event->setDefendantName($existing_name);
                }

            } elseif (false === $literal_match) {
                // there is an inexact duplicate of the name they are
                // turning $defendantName into; id is different
                if (! $duplicate_resolution) {
                    $response = [
                        'inexact_duplicate_found' => 1,
                        'debug' => $debug,
                        'existing_entity'=>(string)$existing_name,
                    ];
                    return $response;
                }
                if (! in_array($duplicate_resolution,
                    [ DefendantForm::UPDATE_EXISTING,
                    DefendantForm::USE_EXISTING]))
                throw new \RuntimeException('invalid value for duplicate resolution');
                if ($duplicate_resolution == DefendantForm::UPDATE_EXISTING) {
                    $existing_name
                        ->setGivenNames($defendantName->getGivenNames())
                        ->setSurnames($defendantName->getSurnames());
                }
                $shit = $this->getDefendantEventsForDefendant($defendantName);
                foreach ($shit as $deft_event) {
                    $deft_event->setDefendantName($existing_name);
                }
                $debug .= sprintf("updated %s defendant-events",count($shit));
            }
            $em->detach($defendantName);
            $em->flush();
            return ['status'=>'success','debug'=>$debug];
        }

        // else, they want to update a subset of defendant-events

        $dql = 'SELECT de FROM InterpretersOffice\Entity\DefendantEvent de
        JOIN de.defendant d JOIN de.event e
        LEFT JOIN e.anonymousJudge aj LEFT JOIN e.judge j
        WHERE d.id = :id AND ';
        $where = [];

        foreach ($occurrences as $occurrence) {
            $data = json_decode($occurrence);
            $where[]  = sprintf(
                "(e.docket = '{$data->docket}' AND %s.id = %d)",
                $data->anonymous ? 'aj' : 'j',
                $data->judge_id
            );
        }
        $string = implode(' OR ',$where);
        if (count($where) > 1) {
            $string = "($string)";
        }
        $dql .= $string;
        $shit = $this->createQuery($dql)
            ->setParameters(['id'=>$defendantName->getId()])
            ->getResult();


        //return ['shit'=> 'got as far as '.__LINE__];
        if (! $existing_name) {
            $debug .= "updating a subset, no existing name, inserting new";
            $new_entity = (new Entity\DefendantName())
                ->setGivenNames($defendantName->getGivenNames())
                ->setSurnames($defendantName->getSurnames());
            $em->persist($new_entity);
            foreach($shit as $deft_event) {
                $deft_event->setDefendantName($new_entity);
            }
            $em->detach($defendantName);
            $em->flush();

            return ['status'=>'success','debug'=>$debug];
        } else {
            //return ['shit'=> 'got as far as '.__LINE__];
        }

        if (false === $literal_match) {
            // there is an inexact duplicate of the name they are
            // turning $defendantName into; id is different
            if (! $duplicate_resolution) {
                $response = [
                    'inexact_duplicate_found' => 1,
                    'debug' => $debug,
                    'existing_entity'=>(string)$existing_name,
                ];
                return $response;
            }
            if (! in_array($duplicate_resolution,
                [ DefendantForm::UPDATE_EXISTING,
                DefendantForm::USE_EXISTING]))
            throw new \RuntimeException('invalid value for duplicate resolution');

            if ($duplicate_resolution == DefendantForm::UPDATE_EXISTING) {
                $existing_name
                    ->setGivenNames($defendantName->getGivenNames())
                    ->setSurnames($defendantName->getSurnames());
            }
            foreach ($shit as $deft_event) {
                $deft_event->setDefendantName($existing_name);
            }
            $debug .= sprintf("updated %s defendant-events",count($shit));
            $em->detach($defendantName);
            $em->flush();

            return ['status'=>'success','debug'=>$debug];
        } else {
            foreach ($shit as $deft_event) {
                $deft_event->setDefendantName($existing_name);
            }
            $em->detach($defendantName);
            $em->flush();

            return ['debug'=>$debug,'shit'=>'we are at '.__LINE__,'moreshit'=>gettype($existing_name)];
        }

        return ['WTF'=> "this should not happen",'debug'=>$debug];
    }
}
