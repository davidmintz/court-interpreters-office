<?php /**  module/Admin/src/Service/Log/Writer.php */

namespace InterpretersOffice\Admin\Service\Log;

use Zend\Log\Writer\AbstractWriter;

/**
 * Writer for logging application-event messages
 * at the INFO level
 *
 * ```
 * $pdo  = new \PDO("mysql:dbname={$params['dbname']}",$params['user'],$params['password']);
 * $log = new Logger();
 * $log->addWriter(new Writer($pdo));
 * $log->info("say shit to my ass",['entity_class'=> "Foo"]);
 * ```
 */
class Writer extends AbstractWriter
{

    /**
     * database
     *
     * @var \PDO
     */
    private $pdo;

    /**
     * sql
     *
     * @var string
     */
    private $sql = 'INSERT INTO app_event_log (timestamp, message, entity_id,
            entity_class, priority, priority_name, extra)
        VALUES (:timestamp, :message, :entity_id, :entity_class,
             :priority, :priorityName, :extra)';

    /**
     * Constructor
     *
     * @param \PDO $pdo
     * @param  array|Traversable $options
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(\PDO $pdo, $options = null)
    {
        $this->pdo = $pdo;
        parent::__construct($options);
    }

    /**
     * implements Zend\Log\Writer\WriterInterface
     *
     * @param  Array  $event
     * @return void
     */
    public function doWrite(Array $event)
    {
        /** @var $db \PDO */
        $db = $this->pdo;
        // move this to a processor class!
        $timestamp = $event['timestamp']->format('Y-m-d H:i:s');
        $params = $event;
        $params['timestamp'] = $timestamp;
        $extra = $event['extra'];
        $defaults = ['entity_id' => null, 'entity_class' => ''];
        foreach (['entity_class','entity_id'] as $field) {
            if (! empty($extra[$field])) {
                $params["{$field}"] = $extra[$field];
                unset($params['extra'][$field]);
            } else {
                $params[$field] = $defaults[$field];
            }
        }
        if (count($params['extra'])) {
            $params['extra'] = json_encode($params['extra']);
            if (strlen($params['extra']) > 5000) {
                $params['extra'] = json_encode(['db_storage_error'=>'"extra" data exceeded 5000 character limit']);
            }
        } else {
            $params['extra'] = '';
        }
        $stmt = $db->prepare($this->sql);
        $stmt->execute($params);
    }
}
