<?php /** module/Admin/src/Service/ReportService.php */

namespace InterpretersOffice\Admin\Service\Reports;

use Doctrine\ORM\EntityManagerInterface;
// use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
// use Doctrine\DBAL\Query\QueryBuilder;

/**
 * generates reports
 */
class ReportService 
{
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
}