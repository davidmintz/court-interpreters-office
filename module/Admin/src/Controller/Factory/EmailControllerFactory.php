<?php /** module/Admin/src/Controller/Factory/EmailControllerFactory.php  */

namespace InterpretersOffice\Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Controller\EmailController;
use InterpretersOffice\Admin\Service\EmailService;

/**
 * EmailControllerFactory
 */
class EmailControllerFactory implements FactoryInterface
{
    /**
     * invoke yadda yadda
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return EmailController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new EmailController($container->get(EmailService::class));
    }
}
