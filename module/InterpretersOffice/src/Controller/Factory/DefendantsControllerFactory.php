<?php
/** module/InterpretersOffice/src/Controller/Factory/DefendantsControllerFactory.php */

namespace InterpretersOffice\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Controller\DefendantsController;

/**
 * Factory for DefendantsController
 */
class DefendantsControllerFactory implements FactoryInterface
{
    /**
     * yadda yadda
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return DefendantsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new DefendantsController(
            $container->get(\Doctrine\ORM\EntityManager::class),
            $container->get("ViewHelperManager")->get("defendantName")
        );
    }
}
