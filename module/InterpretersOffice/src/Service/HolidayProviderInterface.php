<?php /** module/InterpretersOffice/src/Service/HolidayProviderInterface */

namespace InterpretersOffice\Service;

interface HolidayProviderInterface
{

    /**
     * gets holidays between $from and $to inclusive
     *
     * @param  string YYYY-MM-DD
     * @param  string YYYY-MM-DD
     * @return Array of date/strings formatted YYYY-MM-DD
     */
    public function getHolidaysForPeriod($to, $from);

}
