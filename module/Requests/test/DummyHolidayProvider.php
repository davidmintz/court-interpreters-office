<?php
///*
namespace ApplicationTest;
use InterpretersOffice\Service\HolidayProviderInterface;

class DummyHolidayProvider implements HolidayProviderInterface
{
    public function getHolidaysForPeriod($from, $to)
    {
        return [
            "2020-01-01",
            "2019-12-25",
            "2019-12-24",
            "2019-11-11",
            "2019-10-14",
            "2019-09-02",
            "2019-07-05",
            "2019-07-04",
            "2019-05-27",
            "2019-02-18",
            "2019-01-21",
            "2018-12-31",
            "2018-12-25",
            "2018-12-24",
            "2018-11-23",
            "2018-11-22",
            "2018-11-12",
            "2018-10-08",
            "2018-09-03",
            "2018-07-04",
            "2018-05-28",
            "2018-02-19",
            "2018-01-15",
            "2018-01-04",
            "2018-01-01",
        ];
    }
}
//*/
