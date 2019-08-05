<?php
/**
 *  module/InterpretersOffice/src/Entity/Repository/JudgeRepository.php.
 */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
//use Doctrine\ORM\EntityManagerInterface;

use InterpretersOffice\Entity;

/**
 * custom repository class for Judge entity.
 *
 */
class JudgeRepository extends EntityRepository implements CacheDeletionInterface
{
    use ResultCachingQueryTrait;

    /**
     * @var string cache namespace
     */
    protected $cache_namespace = 'judges';

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
        $this->cache->setNamespace($this->cache_namespace);
    }

    /**
     * gets all the Judge entities, sorted.
     *
     * @return array
     */
    public function findAll()
    {
        $dql = 'SELECT j FROM InterpretersOffice\Entity\Judge j '
               .'ORDER BY j.lastname, j.firstname';

        return $this->createQuery($dql, $this->cache_namespace)->getResult();
    }


    /**
     * does entity $id have related entities?
     *
     * returns false if this Judge has no related
     * entities and can therefore safely be deleted
     *
     * @param int $id
     * @return boolean true if there are related entities
     */
    public function hasRelatedEntities($id)
    {
        $q = 'SELECT COUNT(e.id) FROM InterpretersOffice\Entity\Event e JOIN
            e.judge j WHERE j.id = :id';
        // it is theoretically possible a judge could personally request an
        // interpreter and thereby create a related event even without ever
        // being related by event.judge_id, but it seems highly improbable
        $events = $this->getEntityManager()->createQuery($q)
            ->setParameters(['id' => $id])->getSingleScalarResult();
        return $events ? true : false;
    }


    /**
     * gets a listing of judges with default courtrooms
     *
     * @return array
     */
    public function getList()
    {
        if ($this->cache->contains('judges-list')) {
            return $this->cache->fetch('judges-list');
        }
        $dql = 'SELECT j.lastname, j.firstname, j.middlename, f.flavor, j.id,
        j.active, l.name AS location, pl.name AS parent_location
        FROM InterpretersOffice\Entity\Judge j JOIN j.flavor f
        LEFT JOIN j.defaultLocation l LEFT JOIN l.parentLocation pl ORDER BY
            j.lastname,j.firstname,j.middlename';

        $data = $this->createQuery($dql, $this->cache_namespace)->getResult();
        $flavors = array_column($this->createQuery(
            'SELECT f.flavor FROM InterpretersOffice\Entity\JudgeFlavor f
            ORDER BY f.weight'
        )
            ->getResult(), 'flavor');
        $judges = array_combine($flavors, [[],[],[]]);
        foreach ($data as $j) {
            $judges[$j['flavor']][] = $j;
        }
        $this->cache->save('judges-list', $judges);

        return $judges;
    }

    /**
     * gets all the judge entities who are "active"
     *
     * @return array
     */
    public function findAllActive()
    {
        $dql = 'SELECT j FROM InterpretersOffice\Entity\Judge j '
                . ' WHERE j.active = true ORDER BY j.lastname, j.firstname';

        return $this->createQuery($dql, $this->cache_namespace)->getResult();
    }

    /**
     * gets anonymous/generic judges
     *
     * it may be a capital crime to put this here rather than in a separate
     * AnonymousJudgeRepository class, but for now, here it is
     *
     * @return array
     */
    public function getAnonymousJudges()
    {
        $dql = 'SELECT j FROM InterpretersOffice\Entity\AnonymousJudge j';

        return $this->createQuery($dql, $this->cache_namespace)->getResult();
    }

    /**
     * deletes cache
     *
     * implements CacheDeletionInterface
     * @param string $cache_id
     */
    public function deleteCache($cache_id = null)
    {

        $this->cache->setNamespace($this->cache_namespace);
        return $this->cache->deleteAll();
    }
    /**
     * gets data for populating Judge select menu
     *
     * The judge's default courtroom/courthouse location ids are returned as
     * data attributes location and parent_location. If the optional judge_id
     * is provided, we make a point of selecting that judge irrespective of
     * whether that judge is currently flagged as active. In other words, we
     * usually do not want to return inactive judges, but if they're looking a
     * historical data involving an inactive judge, we have to be sure to fetch
     * him or her.
     *
     * @param array $options
     * @return array
     */
    public function getJudgeOptions($options = [])
    {
        $params = [];
        if (isset($options['judge_id'])) {
            $dql = 'SELECT DISTINCT';
            $or  = ' OR j.id = '.$options['judge_id'];
        } else {
            $dql = 'SELECT';
            $or = '';
        }
        $dql .= ' j.id, j.lastname, j.firstname, j.middlename, f.flavor '
                . ', l.id AS location, pl.id AS parent_location'
                . ' FROM InterpretersOffice\Entity\Judge j JOIN j.flavor f '
                . 'LEFT JOIN j.defaultLocation l LEFT JOIN l.parentLocation pl '
                . ' WHERE j.active = true '. $or;

        if (isset($options['user_judge_ids'])) {
            $dql .= ' AND j.id IN (:user_judge_ids)';
            $params['user_judge_ids'] = $options['user_judge_ids'];
        }

        $dql .= ' ORDER BY j.lastname, j.firstname';
        $query = $this->createQuery($dql, $this->cache_namespace);
        if ($params) {
            $query->setParameters($params);
        }
        $judges = $query->getResult();
        $data = [];
        foreach ($judges as $judge) {
            $value = $judge['id'];
            $label = "$judge[lastname], $judge[firstname]";
            if ($judge['middlename']) {
                if (strlen($judge['middlename']) == 2) {
                    $label .= " $judge[middlename]";
                } else { // abbreviate it
                    $label .= " {$judge['middlename'][0]}.";
                }
            }
            $label .= ", $judge[flavor]";
            $attributes = [
                'data-default_location' => $judge['location'],
                'data-default_parent_location' => $judge['parent_location'],
            ];
            $data[] = compact('label', 'value', 'attributes');
        }
        // sort them so that generic magistrate is in the mix, but the
        // "not applicable" and "unknown" are at the bottom
        if (isset($options['include_pseudo_judges'])
                && $options['include_pseudo_judges']) {
            $data = array_merge($data, $this->getPseudoJudgeOptions());
            usort($data, function ($a, $b) {
                if ($this->isAnonymousButNotMagistrate($a)
                   &&
                   ! $this->isAnonymousButNotMagistrate($b)) {
                    return 1;
                } elseif (! $this->isAnonymousButNotMagistrate($a)
                   &&
                   $this->isAnonymousButNotMagistrate($b)) {
                    return -1;
                }
                return strnatcasecmp($a['label'], $b['label']);
            });
        }
        return $data;
    }

    /**
     * is $judge a pseudo-judge known as 'unknown' or 'not applicable'?
     *
     * a helper method for rigging the judge-sorting function that ensures
     * these pseudo-types are last
     *
     * @param array $judge
     * @return boolean
     */
    protected function isAnonymousButNotMagistrate(array $judge)
    {

        return preg_match('/^.?(:?unknown|not applicable)/i', $judge['label']);
    }
    /**
     * gets pseudo-judges
     *
     * helper to get anonymous (a/k/a pseudo-) judges for populating a select
     * elements
     *
     * @return array
     */
    protected function getPseudoJudgeOptions()
    {
        $data = [];
        $pseudojudge_dql = 'SELECT j.id, j.name, l.name as location, l.id '
                . ' AS default_location_id, p.id AS default_parent_location_id, '
                    . 'p.name as parent_location '
                    . 'FROM InterpretersOffice\Entity\AnonymousJudge j '
                    . 'LEFT JOIN j.defaultLocation l '
                    . 'LEFT JOIN l.parentLocation p '
                    . 'ORDER BY j.name, location, parent_location';
        $pseudo_judges = $this->createQuery(
            $pseudojudge_dql,
            $this->cache_namespace
        )->getResult();
        foreach ($pseudo_judges as $pjudge) {
            $value = $pjudge['id'];
            $label = $pjudge['name'];
            if ($pjudge['parent_location']) {
                $label .= " - $pjudge[parent_location]";
            } elseif ($pjudge['location']) {
                $label .= " - $pjudge[location]";
            }
            $attributes = [
                'data-pseudojudge' => 1,
                'data-default_location' => $pjudge['default_location_id'],
                'data-default_parent_location' =>
                    $pjudge['default_parent_location_id'],];
            $data[] = compact('label', 'value', 'attributes');
        }
        return $data;
    }

    /**
     * gets data for judge options
     *
     * @param  stdClass $user
     * @return array
     */
    public function getJudgeOptionsForUser($user)
    {
        // get the Hat entity for $user
        /** @var InterpretersOffice\Entity\Hat $hat */
        $hat = $this->createQuery(
            'SELECT h FROM InterpretersOffice\Entity\Hat h WHERE h.name = :hat'
        )->setParameters(['hat' => $user->hat])->getOneOrNullResult();

        if ($hat->isJudgesStaff()) {
            // just get their judges
            $judges = $this
                ->getJudgeOptions(['user_judge_ids' => $user->judge_ids]);
        } else {
            // e.g., a USPO. get all the judges
            $judges = $this->getJudgeOptions();
            // ... and the pseudo- a/k/a anonymous judge options,
            // except Magistrate
            $pseudo = $this->getPseudoJudgeOptions();
            foreach ($pseudo as $pseudo_judge) {
                if ($this->isAnonymousButNotMagistrate($pseudo_judge)) {
                    $judges[] = $pseudo_judge;
                }
            }
        }

        return $judges;
    }

    /**
     * experimental method. @todo change argument to SomethingInterface
     * of which both Judge and AnonymousJudge are implementations?
     *
     * @param \InterpretersOffice\Entity\AnonymousJudge $judge
     * @return string
     */
    public function getDefaultLocationString(Entity\AnonymousJudge $judge)
    {
        $dql = 'SELECT l.name, p.name AS parent '
                . 'FROM InterpretersOffice\Entity\AnonymousJudge j '
                . 'LEFT JOIN j.defaultLocation l LEFT JOIN l.parentLocation p '
                . 'WHERE j.id = :id';
        $result = $this->createQuery($dql, $this->cache_namespace)
                ->setParameters(['id' => $judge->getId()])
                ->getOneOrNullResult();
        if (! $result or ! $result['name']) {
            return '';
        }
        $name = $result['name'];
        if ($result['parent']) {
            $name .= " $result[parent]";
        }
        return $name;
    }

    public function view(int $id)
    {
        $dql = 'SELECT j, f, h FROM '.Entity\Judge::class.
        ' j JOIN j.flavor f JOIN j.hat h WHERE j.id = :id';

        return $this->getEntityManager()->createQuery($dql)
            ->setParameters(['id'=>$id])
            ->getOneOrNullResult();
    }
}
