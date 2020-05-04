<?php /** module/InterpretersOffice/src/Service/SqlLogger.php */

namespace InterpretersOffice\Service;

use Doctrine\DBAL\Logging\SQLLogger as SqlLoggerInterface;
use Laminas\Log\LoggerInterface;

/**
 * SqlLogger for debugging
 */
class SqlLogger implements SqlLoggerInterface
{

    /**
     * logger
     * 
     * @var LoggerInterface
     */
    private $log;

    /**
     * constructor
     * 
     * @param LoggerInterface $log
     */
    public function __construct(LoggerInterface $log)
    {
        $this->log = $log;
    }
    
    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $entry = $sql . PHP_EOL;

        if ($params) {
            $entry .= print_r($params, true);
        }

        if ($types) {
            //var_dump($types);
        }

        $this->log->debug($entry);
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
    }
}
