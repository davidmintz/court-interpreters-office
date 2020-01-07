<?php /** module/InterpretersOffice/src/Service/Factory/AccountManagerFactory.php */

namespace InterpretersOffice\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Service\AccountManager;

/**
 * factory for AccountManager service
 */
class AccountManagerFactory implements FactoryInterface
{
    /**
     * invoke
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return AccountManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

        $accountManager = new AccountManager(
            $container->get('entity-manager'),
            $container->get('config')
        );
        $accountManager
            ->setLogger($container->get('log'))
            ->setViewRenderer($container->get('ViewRenderer'))
            ->setPluginManager($container->get('ControllerPluginManager'));

        return $accountManager;
    }
}
