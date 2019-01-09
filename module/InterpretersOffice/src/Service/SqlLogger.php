<?php /** module/InterpretersOffice/src/Service/SqlLogger.php */

namespace InterpretersOffice\Service;

use Doctrine\DBAL\Logging\SQLLogger as SqlLoggerInterface;

/**
 * SqlLogger
 */
class SqlLogger implements SqlLoggerInterface
{

    private $log;

    public function __construct($log)
    {
        $this->log = $log;
    }

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
