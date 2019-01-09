<?php
/**
 *  module/InterpretersOffice/src/Entity/Repository/EventTypeRepository.php.
 */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * custom repository class for EventType entity.
 */
class EventTypeRepository extends EntityRepository implements CacheDeletionInterface
{

    use ResultCachingQueryTrait;

    /**
     * cache id
     *
     * @var string $cache_id
     */
    protected $cache_id = 'event-types';

    /**
     * cache
     *
     * @var CacheProvider
     */
    protected $cache;

    /**
     * constructor.
     *
     * @param EntityManagerInterface              $em
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     */
    public function __construct(EntityManagerInterface $em, \Doctrine\ORM\Mapping\ClassMetadata $class)
    {
        $this->cache = $em->getConfiguration()->getResultCacheImpl();
        $this->cache->setNamespace($this->cache_id);
        parent::__construct($em, $class);
    }

    /**
     * gets all the event-types, with sorting.
     *
     * note to self: find out if there's a way to make parent class' findAll()
     * sort for us.
     *
     * @return array
     */
    public function findAll()
    {
        $dql = 'SELECT t FROM InterpretersOffice\Entity\EventType t ORDER BY t.name';

        return $this->createQuery($dql, 0, 'event-types-all')->getResult();
    }

    /**
     * gets data for populating select elements
     *
     * this is used with the admin EventFieldset
     *
     * @param array $options
     * @return array
     */
    public function getEventTypeOptions(array $options = [])
    {
        $dql = 'SELECT t.id AS value, t.name AS label, c.category '
                . 'FROM InterpretersOffice\Entity\EventType t '
                . 'JOIN t.category c ORDER BY label';
        $data = $this->createQuery($dql)->getResult();
        $options = [];
        foreach ($data as $type) {
            $options[] = ['label' => $type['label'], 'value' => $type['value'],
                    'attributes' => ['data-category' => $type['category']],
                ];
        }
        return $options;
    }

    /**
     * gets event-type options for user wearing $hat.
     *
     * @param string $hat
     * @return array
     */
    public function getEventTypesForHat($hat)
    {
        $dql = 'SELECT h.isJudgeStaff FROM InterpretersOffice\Entity\Hat h
            WHERE h.name = :hat';
        $is_judge_staff = $this->createQuery($dql)
            ->setParameters(['hat' => $hat])->getSingleScalarResult();

        $qb = $this->createQueryBuilder('t')->select(
            't.name AS label',
            't.id AS value'
        )->join('t.category', 'c')->orderBy("t.name");

        if ($is_judge_staff) {
            $qb->where("c.category = 'in'");
        } else {
            // alas, we have to hard-code this until we think of something better
            $qb->where("c.category = 'out'")->andWhere(
                "t.name LIKE '%supervision%' OR t.name LIKE '%probation%'"
            );
        }

        return $this->createQuery($qb->getDql())->getResult();
    }

     /**
     * experimental
     *
     * implements cache deletion
     * @param type $cache_id
     */
    public function deleteCache($cache_id = null)
    {

         $this->cache->setNamespace('event-types');
         $this->cache->deleteAll();
         // for debugging
         return sprintf('ran %s at line %d', __METHOD__, __LINE__);
    }

    /**
     * does entity $id have related entities?
     *
     * returns false if this event-type has no related
     * entities and can therefore safely be deleted
     * @param int $id
     * @return boolean true if there are related entities
     */
    public function hasRelatedEntities($id)
    {
        $dql = 'SELECT COUNT(e.id) FROM InterpretersOffice\Entity\Event e
            JOIN e.eventType t WHERE t.id = :id';

        return $this->getEntityManager()->createQuery($dql)->setParameters(
            ['id' => $id]
        )->getSingleScalarResult() ? true : false;
    }
}
