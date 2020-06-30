<?php /** module/InterpretersOffice/src/Service/Factory/RedisFactory.php */

namespace InterpretersOffice\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
// use Redis;

/**
 * factory for Redis instance used for result caching
 */
class RedisFactory implements FactoryInterface
{
    /**
     * implements FactoryInterface.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return Redis $redis instance for result cache
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var Redis $redis */
        $redis = new \Redis();
        $redis->connect("localhost");

        return $redis;
    }
}