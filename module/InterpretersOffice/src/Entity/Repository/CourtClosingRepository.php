<?php
/** module/InterpretersOffice/src/Entity/CourtClosingRepository.php */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Zend\Paginator\Paginator as ZendPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity\CourtClosing;
use InterpretersOffice\Entity\Repository\CacheDeletionInterface;

/**
 * custom EntityRepository class for CourtClosing entity.
 */
class CourtClosingRepository extends EntityRepository implements CacheDeletionInterface
{

    use ResultCachingQueryTrait;

    protected $cache_namespace = 'court-closings';

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
        $config = $em->getConfiguration();
        // $config->addCustomDatetimeFunction('YEAR',
        //     'DoctrineExtensions\Query\Mysql\Year');
        $this->cache = $config->getResultCacheImpl();
        $this->cache->setNamespace($this->cache_namespace);
    }

     /**
      * implements CacheDeletionInterface
      *
      * @param string $cache_namespace
      * @return boolean
      */
    public function deleteCache($cache_id = null)
    {
        $this->cache->setNamespace($this->cache_namespace);
        return $this->cache->deleteAll();
    }

     /**
      * returns a list of court closings
      *
      * @param  int $year optional year
      * @return Array
      */
    public function list($year = null)
    {
        if (! $year) {
            return $this->index();
        }
        $DQL = 'SELECT c, h FROM '.CourtClosing::class . ' c
          LEFT JOIN c.holiday h WHERE SUBSTRING(c.date,1,4) = :year
          ORDER BY c.date ASC';
        $query = $this->createQuery($DQL)
           ->setParameters(['year' => $year]);

        return $query->getArrayResult();
    }

     /**
      * fetches distinct years and number of closings for each
      *
      * @return array
      */
    public function index()
    {
        // not sure how to do this with the QueryBuilder, so...
        $dql = 'SELECT DISTINCT SUBSTRING(c.date,1,4) year, COUNT(c.id) dates
            FROM InterpretersOffice\Entity\CourtClosing c
            GROUP BY year ORDER BY c.date DESC';
        return $this->getEntityManager()->createQuery($dql)->getArrayResult();
    }

    public function getHolidays()
    {
        $dql = 'SELECT h.id AS value, h.name AS label
         FROM InterpretersOffice\Entity\Holiday h
         INDEX BY h.id ORDER BY h.id';

        return $this->createQuery($dql)->getResult();
    }

     /**
      * returns array of dates as of strings YYYY-mm-dd
      * @param string $until date in format YYYY-mm-dd
      * @param string $from date in format YYYY-mm-dd, default is today
      */
    public function getHolidaysForPeriod($until, $from = null)
    {

        if ($from && $from > $until) {
            // swap;
            $tmp = $until;
            $until = $from;
            $from = $tmp;
        }
        if (! $from) {
            $from = date('Y-m-d');
        }
        //$cache = $this->getCacheAdapter();
        $cache = $this->cache;
        $key = "holidays-$from-$until";
        if ($cache && $cache->contains($key)) {
            return $cache->fetch($key);
        }
        $connection = $this->getEntityManager()->getConnection();
        $sql = 'SELECT date FROM court_closings WHERE date BETWEEN :from AND :until and holiday_id IS NOT NULL ORDER BY date';
        $result = $connection->executeQuery($sql, ['from' => $from, 'until' => $until]);
        $data = $result->fetchAll(\PDO::FETCH_COLUMN);
        $cache->save($key, $data);

        return $data;
    }

     /**
      * @param $until \DateTime|string
      * @param $from \DateTime|string
      * @throws \Exception on failure to parse date string
      * @return \DateInterval difference between $from and $until
      */
    public function getDateDiff($until, $from = null)
    {

        // convert parameters to DateTime if necessary
        /** @var \DateTime $until */
        if (is_string($until)) {
            $until = new \DateTime($until);
        }
        /** @var \DateTime $from */
        if (! $from) {
            $from = new \DateTime();
        } elseif (is_string($from)) {
            $from = new \DateTime($from);
        }

        // if $until precedes $from...
        if ($from > $until) {
            $tmp = $from;
            $from = $until;
            $until = $tmp;
            $invert = 1;
        } else {
            $invert = 0;
        }
        // if the start date/time is a weekend or holiday, push the date forward until it isn't
        // and set the time to midnight. unusual, but people might work on a weekend

        $day_of_week = $from->format('w'); // 0 = sunday, 6 = saturday
        $this->debug(sprintf("parameters are from %s and until %s", $from->format('r'), $until->format('r')));
        if ($day_of_week == 0) {
            $from->add(new \DateInterval("P1D"))->setTime(0, 0);
        } elseif ($day_of_week == 6) {
            $from->add(new \DateInterval("P2D"))->setTime(0, 0);
            //print_r($from);
        }
        $from_ymd = $from->format('Y-m-d');

        // if $from is a holiday (also unusual), likewise advance $from until it isn't

        /** @todo  consider fetching ALL closings, ad hoc non-holiday included.
         * if we are closed ~today~ for any reason, then today is not a business day */
        $holidays = $this->getHolidaysForPeriod($until->format('Y-m-d'), $from_ymd);
        $holidays_to_deduct = count($holidays);
        $this->debug(sprintf("%d holidays between submitted dates", count($holidays)));
        while (in_array($from_ymd, $holidays)) {
            $from->add(new \DateInterval("P1D"));
            $from_ymd = $from->format('Y-m-d');
            $holidays_to_deduct--; // already accounted for
        }

        // figure out how many weekend days to deduct
        $diff = $from->diff($until);
        $weeks = floor($diff->days / 7);
        $this->debug("# of weeks is $weeks");

        $days_to_deduct = 2 * $weeks;
        $this->debug("$days_to_deduct days to deduct...");
        $until_day_of_week = $until->format('w');
        if ($until_day_of_week < $day_of_week) {
            $days_to_deduct += 2;
            $this->debug('incrementing $days_to_deduct += 2 ...');
        } elseif ($until_day_of_week == $day_of_week) {
            // then it depends on the time of day
            $t1 = $from->format('H:i');
            $t2 = $until->format('H:i');
            $this->debug("comparing from-time $t1 and until-time $t2");
            if ($t1 >= $t2) {
                $days_to_deduct += 2;
                $this->debug('incrementing $days_to_deduct += 2 ...');
            }
        }
        $this->debug("deducting $holidays_to_deduct holidays");
        $days_to_deduct += $holidays_to_deduct;
        $this->debug("days to deduct is now:  $days_to_deduct at " . __LINE__);
        // figure out how many holidays to deduct
        if ($days_to_deduct) {
            $from->add(new \DateInterval("P{$days_to_deduct}D"));
        }

        $diff = $from->diff($until);
        $this->debug(sprintf("diff: %s\n", $diff->format('%d days, %h hours')));
        $diff->invert = $invert;

        return $diff;
    }

    /**
     * a no-op
     *
     * @param  string $message
     * @return void
     */
    private function debug($message) {

    }
     /**
      * returns a DateTime that is two SDNY business days from $when
      * @param \DateTime $when
      * @return \DateTime
      */
    public function getTwoBusinessDaysFromDate(\DateTime $when = null, $op = 'add')
    {

        if (! $when) {
            $when = new \DateTime();
        } else {
            // i.e., clone
            $when = new \DateTime($when->format('Y-m-d H:i:s'));
        }

        $formatted_when_date = $when->format('Y-m-d');
        $sign = ($op == 'add' ? '+':'-');
        $plus_or_minus_two_weeks = (new \DateTime($when->format('Y-m-d') . " {$sign}2 weeks"))->format('Y-m-d');
        $holidays = $this->getHolidaysForPeriod($plus_or_minus_two_weeks, $when->format('Y-m-d'));
        if (in_array($formatted_when_date, $holidays) or in_array($when->format('N'), [6,7])) {
            //echo "adding 1 day and setting to midnight for non-business day...<br>";
            $when->$op(new \DateInterval("P1D"));
            $when->setTime(0, 0);
        }
        $business_days_added_or_subtracted = 0;
        //echo "<br>\$when is {$when->format('D Y-m-d')}, day of week {$when->format('N')}";
        do {
            $day_of_week = $when->format('N');
            if (in_array($day_of_week, [6,7])) {
                //echo "<br>adjusting 1 day for weekend because dow =  $day_of_week...";
                $when->$op(new \DateInterval("P1D"));
                continue;
            }
            if (in_array($when->format('Y-m-d'), $holidays)) {
                //echo "<br>adjusting 1 day for holiday...";
                $when->$op(new \DateInterval("P1D"));
                continue;
            }
            $when->$op(new \DateInterval("P1D"));
            $business_days_added_or_subtracted++;
        } while ($business_days_added_or_subtracted < 2);

        return $when;
    }
}
