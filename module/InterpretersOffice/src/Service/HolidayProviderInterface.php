<?php /** module/InterpretersOffice/src/Service/HolidayProviderInterface */

namespace InterpretersOffice\Service;

interface HolidayProviderInterface
{

    public function getHolidaysForPeriod($from, $to);

}
