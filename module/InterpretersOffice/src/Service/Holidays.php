<?php
namespace InterpretersOffice\Service;

class Holidays implements HolidayProviderInterface
{

    public function getHolidaysForPeriod($from, $to) {
        return ['2019-02-18'];
    }
}
