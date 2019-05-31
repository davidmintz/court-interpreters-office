<?php /** module/InterpretersOffice/src/Service/DateCalculator.php */

namespace InterpretersOffice\Service;

/**
 * rudimentary business date arithmetic
 */
class DateCalculator
{

    /**
     * data source for holidays
     *
     * @var HolidayProviderInterface
     */
    protected $holidays;

    /**
    * array of date-strings
    *
    * @var array
    */
    protected $current_holidays;

    /**
     * constructor
     *
     * @param HolidayProviderInterface $holidays
     */
    public function __construct(HolidayProviderInterface $holidays)
    {
        $this->holidays = $holidays;
    }

    /**
     * gets datetime two business days after $date
     *
     * @param  \Datetime $date
     * @return \Datetime
     */
    public function getTwoBusinessDaysAfter(\Datetime $date)
    {
        return $this->getTwoBusinessDaysFrom($date, 'add');
    }

    /**
     * computes datetime two days before or after $date
     *
     * @param  DateTime $date
     * @param  string   $op   'add' or 'sub'
     * @return \DateTime
     */
    public function getTwoBusinessDaysFrom(\DateTime $date, $op)
    {
        $from = $date->format('Y-m-d');
        $sign = $op == 'add' ? '+' : '-';
        $to   = (new \DateTime("$from {$sign}2 weeks"));
        $this->current_holidays = $this->holidays->getHolidaysForPeriod(
            $to,
            $from
        );
        if (! $this->isABusinessDay($date)) {
            $date->setTime(0, 0);
            while (! $this->isABusinessDay($date)) {
                $date->$op(new \DateInterval('P1D'));
            }
        }
        $n = 0;
        while ($n < 2) {
            $date->$op(new \DateInterval('P1D'));
            while (! $this->isABusinessDay($date)) {
                $date->$op(new \DateInterval('P1D'));
                continue;
            }
            $n++;
        }

        return $date;
    }

    /**
     * gets datetime two business days before $date
     *
     * @param  \Datetime $date
     * @return \Datetime
     */
    public function getTwoBusinessDaysBefore(\Datetime $date)
    {
        return $this->getTwoBusinessDaysFrom($date, 'sub');
    }

    /**
     * is $date a business day?
     *
     * @param  DateTime $date
     * @return boolean
     */
    public function isABusinessDay(\DateTime $date)
    {
        $dow = $date->format('N');
        if ($dow > 5) {
            return false;
        }
        if (in_array($date->format('Y-m-d'), $this->current_holidays)) {
            return false;
        }

        return true;
    }

    /**
    * gets difference between two dates
    *
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
        // if the start date/time is a weekend or holiday, push the date forward
        // until it isn't and set the time to midnight. unusual, but people might
        // work on a weekend
        $day_of_week = $from->format('w'); // 0 = sunday, 6 = saturday
        // $this->debug(sprintf("parameters are from %s and until %s",
        //      $from->format('r'), $until->format('r')));
        if ($day_of_week == 0) {
            $from->add(new \DateInterval("P1D"))->setTime(0, 0);
        } elseif ($day_of_week == 6) {
            $from->add(new \DateInterval("P2D"))->setTime(0, 0);
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
        // $this->debug("# of weeks is $weeks");

        $days_to_deduct = 2 * $weeks;
        $until_day_of_week = $until->format('w');
        if ($until_day_of_week < $day_of_week) {
            $days_to_deduct += 2;
        } elseif ($until_day_of_week == $day_of_week) {
            // then it depends on the time of day
            $t1 = $from->format('H:i');
            $t2 = $until->format('H:i');
            if ($t1 >= $t2) {
                $days_to_deduct += 2;
                $this->debug('incrementing $days_to_deduct += 2 ...');
            }
        }
        $days_to_deduct += $holidays_to_deduct;
        // $this->debug("days to deduct is now:  $days_to_deduct at " . __LINE__);
        // figure out how many holidays to deduct
        if ($days_to_deduct) {
            $from->add(new \DateInterval("P{$days_to_deduct}D"));
        }

        $diff = $from->diff($until);
        $diff->invert = $invert;

        return $diff;
    }

   /**
    * a noop
    * @param  string $msg
    */
    protected function debug($msg)
    {
    }
}
