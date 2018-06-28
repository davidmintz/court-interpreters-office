<?php /** module/InterpretersOffice/src/Service/Factory/AccountManagerFactory.php */

namespace InterpretersOffice\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Service\AccountManager;

/**
 * factory for AccountManager service
 */
class AccountManagerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return AccountManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // inject dependencies, to be continued...
        return (new AccountManager(
            $container->get('entity-manager')
            ))->setLogger($container->get('log'));
    }
}
