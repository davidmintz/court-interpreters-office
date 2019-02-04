<?php

namespace InterpretersOffice\Service;

class DateCalculator
{
    protected $holidays;

    public function __construct(HolidayProviderInterface $holidays)
    {
        $this->holidays = $holidays;
    }

    protected $current_holidays;

    public function getTwoBusinessDaysAfter(\Datetime $date)
    {
        $from = $date->format('Y-m-d');
        $to   = (new \DateTime("$from +2 weeks"));
        $this->current_holidays = $this->holidays->getHolidaysForPeriod(
            $from, $to
        );
        printf("\nbeginning with: $from\n");
        $n = 0;
        //$date->add(new \DateInterval('P2D'));
        printf("\nnow we're at : {$date->format('Y-m-d D')}\n");
        while ($n < 2) {
            $date->add(new \DateInterval('P1D'));
            while(! $this->isABusinessDay($date)) {
                $date->add(new \DateInterval('P1D'));
                continue;
            }
            $n++;
        }

        return $date;
    }

    public function isABusinessDay(\DateTime $date)
    {
        $dow = $date->format('N');
        if ($dow > 5) {
            printf("\n\$dow = $dow, returning false\n");
            return false;
        }
        if (in_array($date->format('Y-m-d'),$this->current_holidays)) {
            printf("\n{$date->format('Y-m-d D')} is a holiday, returning false\n");
            return false;
        }
        printf("\n{$date->format('Y-m-d D')} not a weekend or holiday, returning true");
        return true;

    }


}
