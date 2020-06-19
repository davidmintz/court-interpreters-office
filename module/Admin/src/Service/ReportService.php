<?php /** module/Admin/src/Service/ReportService.php */

declare(strict_types=1);

namespace InterpretersOffice\Admin\Service;
use Laminas\InputFilter\Factory;
use Laminas\InputFilter\InputFilter;
use Doctrine\ORM\EntityManagerInterface;


// use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
// use Doctrine\DBAL\Query\QueryBuilder;

/**
 * generates reports
 */
class ReportService 
{
    

    const REPORT_USAGE_BY_LANGUAGE = 1;
    const REPORT_USAGE_BY_INTERPRETER = 2;
    const REPORT_CANCELLATIONS_BY_JUDGE = 3;
    const REPORT_BELATED_BY_JUDGE = 4;

    /**
     * @var array
     * 
     * report id => label
     */
    private static $reports = [
        self::REPORT_USAGE_BY_LANGUAGE => 'interpreter usage by language',
        self::REPORT_USAGE_BY_INTERPRETER => 'interpreter usage by interpreter',
        self::REPORT_CANCELLATIONS_BY_JUDGE => 'belated cancellations per judge',
        self::REPORT_BELATED_BY_JUDGE => 'belated requests per judge',
    ];

    /**
     * @var array
     * 
     * date ranges for form
     */
    private static $date_range_options = [
        1 => 'current calendar year to date',
            'current calendar year to date',
            'current quarter to date',
            'previous year',
            'previous quarter',
            'current fiscal year to date',
            'previous fiscal year',
            'custom...'
    ];

    /**
     * entity manager
     *
     * @var EntityManagerInterface $em
     */
    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * gets report ids => labels
     * 
     * @return array
     */
    public function getReports() : array
    {
        return self::$reports;
    }

    /**
     * gets date-range options
     * 
     * @return array
     */
    public function getDateRangeOptions() : array
    {
        return self::$date_range_options;
    }

    /**
     * gets inputfilter
     * 
     * @return InputFilter
     */
    public function getInputFilter() : InputFilter
    {
        return (new Factory())->createInputFilter($this->getInputSpecification());
    }

    public function getInputSpecification() : array
    {
        return [
            'report' => [
                'name' => 'report',
                'required' => false,
                'validators' => [],
                'filters' => [],
            ],
            'date-range'=> [
                'name' => 'date-range',
                'required' => false,
                'validators' => [],
                'filters' => [],
            ],
            'date-from' => [
                'name' => 'date-from',
                'required' => true,
                'validators' => [],
                'filters' => [],
            ],
            'date-to' => [
                'name' => 'date-to',
                'required' => true,
                'validators' => [],
                'filters' => [],
            ],
        ];
    }


}