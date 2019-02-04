<?php /** module/Requests/test/DateCalculationTest.php */
namespace ApplicationTest;
use PHPUnit\Framework\TestCase;
use InterpretersOffice\Service\DateCalculator;

class DateCalculationTest extends TestCase

{
    protected $calculator;

    public function setUp()
    {
        $this->calculator = new DateCalculator(new DummyHolidayProvider());
    }
    public function testTwoBusinessDaysAfter()
    {
        $shits = [

             ['date'=> 'Mon 2019-07-01 9:47 am','expected'=> 'Wed 2019-07-03 9:47 am'],
             ['date'=> 'Tue 2019-07-02 2:10 pm','expected'=> 'Mon 2019-07-08 2:10 pm'],
             ['date'=> 'Wed 2019-07-03 10:30 am','expected'=> 'Tue 2019-07-09 10:30 am'],
             ['date'=> 'Mon 2019-02-04 11:40 am','expected'=> 'Wed 2019-02-06 11:40 am'],
             ['date'=> 'Tue 2019-02-05 6:12 pm','expected'=> 'Thu 2019-02-07 6:12 pm'],
             ['date'=> 'Wed 2019-02-06 3:57 pm','expected'=> 'Fri 2019-02-08 3:57 pm'],
             ['date'=> 'Thu 2019-02-07 8:17 am','expected'=> 'Mon 2019-02-11 8:17 am'],
             ['date'=> 'Fri 2019-02-08 12:33 pm','expected'=> 'Tue 2019-02-12 12:33 pm'],
             ['date'=> 'Sat 2019-02-09 10:22 am','expected'=> 'Wed 2019-02-13 12:00 am'],
             ['date'=> 'Sun 2019-02-10 2:34 pm','expected'=> 'Wed 2019-02-13 12:00 am'],
             ['date'=> 'Fri 2019-02-15 4:42 pm','expected'=> 'Wed 2019-02-20 4:42 pm'],
        ];

        foreach ($shits as $shit) {

            $result = $this->calculator
                ->getTwoBusinessDaysAfter(new \DateTime($shit['date']));
            $this->assertEquals(
                $shit['expected'],
                $result->format('D Y-m-d g:i a'),
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
