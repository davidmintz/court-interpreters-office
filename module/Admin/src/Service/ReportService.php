<?php /** module/Admin/src/Service/ReportService.php */

declare(strict_types=1);

namespace InterpretersOffice\Admin\Service;
use Laminas\InputFilter\Factory;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;


/**
 * generates reports
 */
class ReportService 
{
    
    /* SELECT l.name language, COUNT(ie.event_id) AS total FROM languages l 
        JOIN events e ON l.id = e.language_id 
        JOIN interpreters_events ie ON ie.event_id = e.id 
        GROUP BY l.name ORDER BY `total` DESC;
    */

    

    /*
 $qb->select(['l.name','SUM(CASE WHEN e.cancellation_reason IS NOT NULL THEN 1 ELSE 0 END) AS cancelled','COUNT(e.id) AS total'])
        ->from('InterpretersOffice\Entity\Event', 'e')->join('e.language','l',NULL)->groupBy('l.name');

    */
    const REPORT_USAGE_BY_LANGUAGE = 1;
    const REPORT_USAGE_BY_INTERPRETER = 2;
    const REPORT_CANCELLATIONS_BY_JUDGE = 3;
    const REPORT_BELATED_BY_JUDGE = 4;

    /**
     * report id => label
     * @var array     
     */
    private static $reports = [
        self::REPORT_USAGE_BY_LANGUAGE => 'interpreter usage by language',
        self::REPORT_USAGE_BY_INTERPRETER => 'interpreter usage by interpreter',
        self::REPORT_CANCELLATIONS_BY_JUDGE => 'belated cancellations per judge',
        self::REPORT_BELATED_BY_JUDGE => 'belated requests per judge',
    ];

    /**
     * date ranges for form
     * @var array
     */
    private static $date_range_options = [
        'YTD' =>  'year to date',
        'QTD'=>   'current quarter to date',
        'PY'=>    'previous year',
        'PQ'=>    'previous quarter',
        'FYTD'=>  'fiscal year to date',
        'PFY'=>   'previous fiscal year',
        'CUSTOM'=>'custom...'
    ];

    /**
     * entity manager
     * @var EntityManagerInterface $em
     */
    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createReport(Array $options) {
        if (!isset($options['report'])) {
            throw new \RuntimeException(sprintf('missing report "report" option in %s',__FUNCTION__));
        }
        $from= new \DateTime($options['date-from']);
        $to = new \DateTime($options['date-to']);
        $qb = $this->em->createQueryBuilder();
        switch($options['report']) {
            case self::REPORT_USAGE_BY_LANGUAGE:
                $data = $this->createLanguageUsageQuery($qb)
                    ->where($qb->expr()->between('e.date',':from',':to'))
                    ->setParameters([':from'=>$from, ':to' => $to])
                    ->getQuery()->getResult();
                $totals = ['completed' => 0,'cancelled'=> 0];
                foreach($data as $i => $record) {
                   $record['completed'] = $record['total'] - $record['cancelled'];
                   $totals['completed'] += $record['completed'];
                   $totals['cancelled'] += $record['cancelled'];
                   $data[$i] = $record;
                }
            break;
        }

        return [            
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'totals' => $totals,
            'data' => $data ,
        ];
    }

    public function createLanguageUsageQuery(QueryBuilder $qb) {
    /*
    SELECT l.name language, SUM(IF(c.category="in",1,0)) as `in-court`, SUM(IF(c.category = "out",1,0)) as `ex-court`, 
        COUNT(ie.event_id) AS total FROM languages l JOIN events e ON l.id = e.language_id 
        JOIN interpreters_events ie ON  ie.event_id = e.id JOIN event_types t ON e.event_type_id = t.id  
        JOIN event_categories c ON t.category_id = c.id  
        GROUP BY l.name ORDER BY `total` DESC LIMIT 20;
    */
        return $qb->select(['l.name AS language',
        'SUM(CASE WHEN e.cancellation_reason IS NOT NULL THEN 1 ELSE 0 END) AS cancelled',
        'SUM(CASE WHEN c.category = \'in\' THEN 1 ELSE 0 END) AS in_court',
        'SUM(CASE WHEN c.category = \'out\' THEN 1 ELSE 0 END) AS ex_court',
        'COUNT(ie.event) AS total'])
        ->from('InterpretersOffice\Entity\Event', 'e')
        ->join('e.interpreterEvents','ie')
        ->join('e.language','l')
        ->join('e.event_type','t')
        ->join('t.category','c')
        ->orderBy('total','DESC')
        ->groupBy('l.name');      
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
        return (new Factory())->createInputFilter($this->getInputFilterSpecification());
    }


    /**
     * gets inputfilter specification
     * 
     * @return array
     */
    public function getInputFilterSpecification() : array
    {
        return [
            'report' => [
                'name' => 'report',
                'required' => true,
                'validators' => [
                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'messages' => [
                                Validator\NotEmpty::IS_EMPTY => "report type is required",
                            ]
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => Validator\InArray::class,
                        'options' => [
                            'haystack' => array_keys(self::$reports),
                            'messages' => [
                                Validator\InArray::NOT_IN_ARRAY =>
                                 'invalid type: %value%',
                            ],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    // [
                    //     'name' => Validator\InArray::class,
                    //     'options' => [
                    //         'messages' => [
                    //             Validator\InArray::NOT_IN_ARRAY => "invalid report type",
                    //             'haystack' => array_keys($this->getReports()),
                    //         ]
                    //     ],
                    //     'break_chain_on_failure' => true,
                    // ],
                ],
                'filters' => [],
            ],
            // 'date-range'=> [
            //     'name' => 'date-range',
            //     'required' => false,
            //     'validators' => [],
            //     'filters' => [],
            // ],
            'date-from' => [
                'name' => 'date-from',
                'required' => true,
                'validators' => [
                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'messages' => [
                                Validator\NotEmpty::IS_EMPTY => '"from" date is required',
                            ]
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => 'Date',
                        'options' => [
                            'format'=>'m/d/Y',
                            'messages'=> ['dateInvalidDate'=>'date is invalid',]
                        ],
                        'break_chain_on_failure' => true,
                    ],
                ],
                'filters' => [],
            ],
            'date-to' => [
                'name' => 'date-to',
                'required' => true,
                'validators' => [
                    [
                        'name' => Validator\NotEmpty::class,
                        'options' => [
                            'messages' => [
                                Validator\NotEmpty::IS_EMPTY => '"to" date is required',
                            ]
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => 'Date',
                        'options' => [
                            'format'=>'m/d/Y',
                            'messages'=>['dateInvalidDate'=>'date is invalid']
                        ],
                        'break_chain_on_failure' => true,
                    ],
                ],
                'filters' => [],
            ],
        ];
    }
}
