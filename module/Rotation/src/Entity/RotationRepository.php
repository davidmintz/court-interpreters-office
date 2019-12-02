<?php /** module/Rotation/src/Entity/RotationRepository.php */

namespace InterpretersOffice\Admin\Rotation\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\ORM\QueryBuilder;
use InterpretersOffice\Entity\Person;
//
use InterpretersOffice\Entity\Repository\CacheDeletionInterface;
use PHPUnit\Util\Log\TeamCity;
use DateTime, DateInterval, DateTimeImmutable, DateTimeInterface;

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

    public function getTask($id)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('t, r, m, p')->from(Task::class, 't')
        ->join('t.rotations','r')
        ->leftJoin('r.members','m')
        ->leftJoin('m.person','p')
        ->where('t.id = :id')
        ->setParameters(['id'=>$id]);
        $q = $qb->getQuery()->useResultCache(true);

        return $q->getOneOrNullResult();
    }

    /**
     * helper to rewind date back to preceding Monday
     *
     * @param  DateTime $date
     * @return DateTimeInterface
     */
    public function getMondayPreceding(DateTime $date) : DateTimeInterface
    {
        $dow = (int)$date->format('N');
        if (1 == $dow) {
            return $date;
        } else {
            $date = DateTimeImmutable::createFromMutable($date);
            $interval = sprintf('P%sD',$dow - 1);
            return $date->sub(new DateInterval($interval));
        }
    }

    /**
     * gets default and assigned Person entities assigned to $task on $date
     *
     * @param  Task     $task
     * @param  DateTime $date
     * @throws \RuntimeException
     * @return Array
     */
    public function getAssignment(Task $task, DateTime $date) : Array
    {
        $frequency = $task->getFrequency();
        if ('WEEK' != $frequency) {
            throw new \RuntimeException("only Tasks of frequency 'WEEK' are currently supported");
        }
        // if the Task has a day-of-week
        // and the $date we've been passed is for a different day-of-the-week
        // then we crank up the date
        $dow = $task->getDayOfWeek();
        $N = $date->format('N');
        if ($dow && $dow != $N) {
            $d = $N > $dow ? 8 - $N : abs($N - $dow);
            // printf("\nDEBUG: task %s dow is: %s, adding %s\n",$task->getName(),$dow, $d);
            $date->add(new DateInterval("P{$d}D"));
            // printf("\nDEBUG: date dow is now: %s\n",$date->format("D"));
        }
        /**
         * @var \Doctrine\ORM\QueryBuilder $qb
         */
        $qb = $this->getEntityManager()->createQueryBuilder();

        $monday = $this->getMondayPreceding($date);
        $params = compact('task','date','monday');
        $qb->select('s, p, h')->from(Substitution::class, 's')
             ->leftJoin('s.person','p')
             ->leftJoin('p.hat','h')
             ->where('s.task = :task');
        $qb->andWhere(
            '(s.date = :date OR (s.date = :monday AND s.duration = \'WEEK\'))'
        );
        $monday = $this->getMondayPreceding($date);
        $params = compact('task','date','monday');
        $qb->setParameters($params);
        $substitution = $qb->getQuery()
            ->useResultCache(true)->getOneOrNullResult();
        $result = $this->getDefaultAssignment($task, $date);

        return [
            'date'  => $date->format('Y-m-d'),
            'default' => $result['default'],
            'assigned' => $substitution ? $substitution->getPerson() : $result['default'],
            'rotation' => $result['rotation'],
            'start_date' => $result['start_date'],
        ];
    }

    /**
     * gets default Person assigned to $task on $date
     * @param  Task     $task
     * @param  DateTime $date
     * @throws \RuntimeException
     * @return Person
     */
    public function getDefaultAssignment(Task $task, DateTime $date) :?Array
    {
        $frequency = $task->getFrequency();
        if ('WEEK' != $frequency) {
            throw new \RuntimeException("only Tasks of frequency 'WEEK' are currently supported");
        }
        // get the most recently begun rotation
        $em = $this->getEntityManager();
        $dow = $task->getDayOfWeek();
        $w = $date->format('w');
        if ($dow && $dow != $w) {
            // push date up to the appropriate day of the week
            $n = 6 - $w;
            $date->add(new DateInterval("P{$n}D"));
        }
        //, p, h, role LEFT JOIN m.person p LEFT JOIN p.hat h LEFT JOIN h.role role
        $q = $em->createQuery('SELECT r, t, m, p FROM '.Rotation::class. ' r
            JOIN r.task t
            LEFT JOIN r.members m
            LEFT JOIN m.person p
            WHERE t.id = :task_id AND r.start_date <= :date
            ORDER BY r.start_date DESC')
            ->setMaxResults(1)
            ->setParameters(['date'=>$date, 'task_id' => $task->getId()])
            ->useResultCache(true);
        $rotation = $q->getOneOrNullResult();
        if (!$rotation) { return null; }
        $members = $rotation->getMembers();
        // debuggery
        // if ($rotation->getId() == 14) {
        //     $j = 0;
        //     printf("rotation id is: %d\n",$rotation->getId());
        //     foreach ($members as $m) {
        //         printf("\n===============\n$j: %s in position %d\n",$m->getPerson()->getFirstName(), $m->getOrder());
        //         $j++;
        //     }
        // }
        $monday = $this->getMondayPreceding($date);
        $start_date = $this->getMondayPreceding($rotation->getStartDate());
        $diff = $monday->diff($start_date);
        $weeks = $diff->format('%a') / 7;
        $i = $weeks % $members->count();

        return [
            'start_date' => $rotation->getStartDate(),
            'rotation' =>$members,
            'default'=> $members[$i]->getPerson()
        ];
    }

/*
SELECT t.name, p.firstname, m.* FROM people p JOIN task_rotation_members m ON p.id = m.person_id
JOIN rotations r ON m.rotation_id = r.id JOIN tasks t ON r.task_id = t.id WHERE t.id = 2
AND r.start_date = (SELECT MAX(start_date) FROM rotations WHERE start_date <= CURDATE() AND task_id = 2
ORDER BY start_date LIMIT 1) ORDER BY rotation_order;
*/

}
