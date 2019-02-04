<?php

namespace InterpretersOffice\Service;

class DateCalculator
{
    protected $holidays;

    public function __construct(HolidayProviderInterface $holidays)
    {
        $this->holidays = $holidays;
    }

    public function getTwoBusinessDaysAfter(\Datetime $date)
    {
        $date->add(new \DateInterval('P2D'));
        while($date->format('N') > 5) {
            $date->add(new \DateInterval('P2D'));
        }
        return $date;
    }

}
