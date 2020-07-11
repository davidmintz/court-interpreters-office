<?php /** module/InterpretersOffice/src/Service/Factory/RedisFactory.php */
declare(strict_types=1);

namespace InterpretersOffice\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Redis;

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
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Redis
    {
        $host = $container->get('config')['redis']['host'] ?? 'localhost';
        /** @var Redis $redis */
        $redis = new \Redis();        
        $redis->connect($host);

        return $redis;
    }
}