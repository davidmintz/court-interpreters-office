<?php /** module/Requests/test/DateCalculationTest.php */
namespace ApplicationTest;
use PHPUnit\Framework\TestCase;

use InterpretersOffice\Service\DateCalculator;
use InterpretersOffice\Service\Holidays;

class DateCalculationTest extends TestCase
{
    protected $calculator;

    public function setUp()
    {
        $this->calculator = new DateCalculator(new Holidays);
    }
    public function testTwoBusinessDaysAfter()
    {
        // $monday = new \DateTime('Mon 2019-02-04 10:00 am');
        // $expected = 'Wed 2019-02-06 10:00 am';
        // $this->assertEquals(
        //     $expected,
        //     getTwoBusinessDaysAfter($monday)->format('D Y-m-d g:i a')
        // );
        // $tuesday = new \DateTime('Tue 2019-02-05 10:00 am');
        // $expected = 'Thu 2019-02-07 10:00 am';
        // $this->assertEquals(
        //     $expected,
        //     getTwoBusinessDaysAfter($tuesday)->format('D Y-m-d g:i a')
        // );
        $shits = [
             ['date'=> 'Mon 2019-02-04','expected'=> 'Wed 2019-02-06'],
             ['date'=> 'Tue 2019-02-05','expected'=> 'Thu 2019-02-07'],
             ['date'=> 'Wed 2019-02-06','expected'=> 'Fri 2019-02-08'],
             ['date'=> 'Thu 2019-02-07','expected'=> 'Mon 2019-02-11'],
             ['date'=> 'Fri 2019-02-08','expected'=> 'Tue 2019-02-12'],
             ['date'=> 'Fri 2019-02-15','expected'=> 'Wed 2019-02-20'],
        ];

        $this->assertTrue($this->calculator instanceof DateCalculator);


        foreach ($shits as $shit) {
            $this->assertEquals(
                $shit['expected'],

                $this->calculator->getTwoBusinessDaysAfter(new \DateTime($shit['date']))->format('D Y-m-d'),
                'failed getting two days after '.$shit['date']
            );
        }
    }


}
/*
February 2019
Su Mo Tu We Th Fr Sa
                1  2
 3  4  5  6  7  8  9
10 11 12 13 14 15 16
17 18 19 20 21 22 23
24 25 26 27 28

 */

function doSomething()
{
    return true;
}

function getTwoBusinessDaysAfter(\DateTime $date = null)
{
    if (!$date) {
        $date = new \DateTime();
    }
    $holidays = getHolidaysForPeriod($date->format("Y-m-d"));
    $days_incremented = 0;
    while ($days_incremented < 2) {
        $date->add(new \DateInterval('P1D'));
        if (isAWeekend($date)) {
            $date->add(new \DateInterval('P1D'));
            continue;
        }
        if (isAHoliday($date->format("Y-m-d"))) {
            $date->add(new \DateInterval('P1D'));
            continue;
        }
        $days_incremented++;
    }
    return $date;
}

function isAWeekend(\DateTime $date)
{
    return in_array($date->format('N'),[6,7]);
}

function isAHoliday($date)
{
    return in_array($date,getHolidaysForPeriod(
        new \DateTime("$date +2 weeks")
    ));
}

function getHolidaysForPeriod($until, $from = null)
{
    return ['2019-02-18'];
}
