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
class EventTypeRepository extends EntityRepository
{
    use ResultCachingQueryTrait;
    
    /**
     * cache id
     * 
     * @var string $cache_id
     */
    protected $cache_id = 'event-types';

    /**
     * constructor.
     *
     * @param EntityManagerInterface              $em
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     */
    public function __construct(EntityManagerInterface $em, \Doctrine\ORM\Mapping\ClassMetadata $class)
    {
        $em->getConfiguration()->getResultCacheImpl()->setNamespace('event-types');
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

        return $this->createQuery($dql)->getResult();
    }
}
