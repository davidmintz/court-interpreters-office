<?php
/**
 * module/InterpretersOffice/src/Service/Factory/AuthenticationFactory.php.
 */

namespace InterpretersOffice\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Log\Logger;
use Laminas\Log\Writer\Stream;
use InterpretersOffice\Admin\Service\Log\Writer as DbWriter;
use Laminas\Log\Filter\Priority as Filter;

/**
 * Factory for instantiating application's Logger instance.
 */
class LogFactory implements FactoryInterface
{
    /**
     * implements FactoryInterface.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return Logger $log a Laminas\Log\Logger instance
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $log = new Logger();
        $path = getcwd().'/data/log/app.log.'.date('Y-m-d');
        $writer = new Stream($path, 'a');
        /** @todo make verbosity level an environment-dependent config variable */
        $filter = new Filter(Logger::DEBUG);
        $writer->addFilter($filter);
        // I think the 2nd argument 'priority' means the order
        // in which writers write, not the filter
        $log->addWriter($writer);
        // $pdo = $container->get('entity-manager')
        //     ->getConnection()->getWrappedConnection();
        //$writer = new DbWriter($pdo);
        // $log->addWriter($container->get(DbWriter::class));
        //    ->getConnection()->getWrappedConnection();
        //$db_writer = new DbWriter($pdo);
        //$db_writer->addFilter(new Priority(Logger::INFO));
        // for now, mark the (approximate) beginning of each request cycle
        //$log->debug("\n================================\n");

        return $log;
    }
}
