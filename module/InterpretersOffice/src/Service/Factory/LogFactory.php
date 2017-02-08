<?php
/**
 * module/InterpretersOffice/src/Service/Factory/AuthenticationFactory.php.
 */

namespace InterpretersOffice\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\Log\Filter\Priority as Filter;

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
     * @return Logger $log a Zend\Log\Logger instance
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $log = new Logger();
        $path = getcwd().'/data/log/app.log';
        $writer = new Stream($path, 'a');
        /** @todo make verbosity level an environment-dependent config variable */
        $filter = new Filter(Logger::DEBUG);
        $writer->addFilter($filter);
        // I think the 2nd argument 'priority' means the order
        // in which writers write, not the filter
        $log->addWriter($writer);
        // for now, mark the (approximate) beginning of each request cycle
        $log->debug("\n================================\n");

        return $log;
    }
}
