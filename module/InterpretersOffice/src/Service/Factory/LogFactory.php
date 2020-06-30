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
        /* make verbosity level environment-dependent */
        $level = getenv('environment') == 'production' ? Logger::INFO : Logger::DEBUG;
        $filter = new Filter($level);
        $writer->addFilter($filter);

        // I think the 2nd argument 'priority' means the order
        // in which writers write, not the filter

        $log->addWriter($writer);
        $log->addWriter($container->get(DbWriter::class));

        return $log;
    }
}
