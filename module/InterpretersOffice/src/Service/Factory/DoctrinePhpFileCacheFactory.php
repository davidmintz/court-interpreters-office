<?php
/**
 * module/InterpretersOffice/src/Service/Factory/DoctrinePhpFileCacheFactory.php
 */
declare(strict_types=1);

namespace InterpretersOffice\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Doctrine\Common\Cache\PhpFileCache;

class DoctrinePhpFileCacheFactory implements FactoryInterface
{
    /**
     * implements FactoryInterface.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     * @return PhpFileCache
     * 
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PhpFileCache
    {
        return new PhpFileCache('data/cache');
    }
}