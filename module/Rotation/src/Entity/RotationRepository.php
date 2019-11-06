<?php /** module/Rotation/src/Entity/RotationRepository.php */

namespace InterpretersOffice\Admin\Rotation\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\QueryBuilder;
use InterpretersOffice\Entity\Person;
//
use InterpretersOffice\Entity\Repository\CacheDeletionInterface;
use DateTime, DateTimeImmutable;

/**
 * Rotation repository
 *
 */
class RotationRepository extends EntityRepository implements CacheDeletionInterface
{
    /**
     * cache namespace
     *
     * @var string
     */
    protected $cache_namespace = 'rotations';

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
     * implements cache deletion
     */
    public function deleteCache($cache_id = null)
    {
        $cache = $this->cache;
        $cache->setNamespace($this->cache_namespace);
        $cache->deleteAll();
    }

    public function getMondayPreceding(DateTime $date) {
        $dow = (int)$date->format('N');
        if (1 == $dow) { return $date; }
        $interval = sprintf('P%sD',$dow - 1);
        $date->sub(new \DateInterval($interval));

        return $date;
    }
    /**
     * gets default and substitute Person entities assigned to $task on $date
     * 
     * @param  Task     $task
     * @param  DateTime $date
     * @throws \RuntimeException
     * @return Array
     */
    public function getAssignedPerson(Task $task, DateTime $date) : Array
    {
        $frequency = $task->getFrequency();
        if ('WEEK' != $frequency) {
            throw new \RuntimeException("only Tasks of frequency 'WEEK' are currently supported");
        }
        $q = $this->getEntityManager()->createQuery(
            'SELECT s FROM '.Substitution::class. ' s
            WHERE s.task = :task AND s.date = :date'
        )   ->useResultCache(true)
            ->setParameters(compact('task','date'));
        $substitution = $q->getOneOrNullResult();
        $default = $this->getDefaultAssignedPerson($task, $date);
        return [
            'default' => $default,
            'assigned' => $substitution ? $substitution->getPerson() : $default
        ];
    }

    /**
     * gets default Person assigned to $task on $date
     * @param  Task     $task
     * @param  DateTime $date
     * @throws \RuntimeException
     * @return Person
     */
    public function getDefaultAssignedPerson(Task $task, DateTime $date) :?Person
    {
        $frequency = $task->getFrequency();
        if ('WEEK' != $frequency) {
            throw new \RuntimeException("only Tasks of frequency 'WEEK' are currently supported");
        }
        // get the most recently begun rotation
        $em = $this->getEntityManager();
        $q = $em->createQuery('SELECT r FROM '.Rotation::class. ' r
            JOIN r.task t WHERE t.id = :task_id AND r.start_date <= :date ORDER BY r.start_date DESC
        ')
            ->setMaxResults(1)
            ->setParameters(['date'=>$date, 'task_id' => $task->getId()])
            ->useResultCache(true);
        $rotation = $q->getOneOrNullResult();
        if (!$rotation) { return null; }
        $members = $rotation->getMembers();
        $monday = $this->getMondayPreceding($date);
        $start_date = $this->getMondayPreceding($rotation->getStartDate());
        // DEBUG
        // printf(
        //     "\nmonday is %s; start is %s",$monday->format('D d-M-Y'),$start_date->format('D d-M-Y')
        // );
        $diff = $monday->diff($start_date);
        $weeks = $diff->format('%a') / 7;
        $i = $weeks % $members->count();
        //printf("\nweeks is $weeks, \$i is %s\n",$i);
        $person = null;
        foreach($members as $m) {
            if ($m->getOrder() == $i) {
                $person = $m->getPerson();
                break;
            }
        }

        return $person;
    }
/*
        $diff = $monday_preceeding_date->diff($monday_preceeding_start);
        $weeks = $diff->format('%a') / 7;
        $names = json_decode($rotation->rotation);
        $i = $weeks % count($names);
        return ['who' => $names[$i],'when' =>  $monday_preceeding_date , 'rotation' => $names];


 */

/*
SELECT t.name, p.firstname, m.* FROM people p JOIN task_rotation_members m ON p.id = m.person_id
JOIN rotations r ON m.rotation_id = r.id JOIN tasks t ON r.task_id = t.id WHERE t.id = 2
AND r.start_date = (SELECT MAX(start_date) FROM rotations WHERE start_date <= CURDATE() AND task_id = 2
ORDER BY start_date LIMIT 1) ORDER BY rotation_order;
*/

}
