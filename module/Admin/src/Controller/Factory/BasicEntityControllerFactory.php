<?php
/** module/InterpretersOffice/src/Controller/Factory/BasicEntityControllerFactory.php */

namespace InterpretersOffice\Admin\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use InterpretersOffice\Entity\Listener;
use Doctrine\ORM\Events;
use Laminas\Filter\Word\CamelCaseToDash as Filter;


/**
 * Factory for instantiating Controllers for managing our relatively
 * simple entities.
 */
class BasicEntityControllerFactory implements FactoryInterface
{
    /**
     * instantiates and returns a concrete instance of AbstractActionController.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return Laminas\Mvc\Controller\AbstractActionController
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        $basename = substr($requestedName, strrpos($requestedName, '\\') + 1);
        $shortName = strtolower((new Filter)
            ->filter(substr($basename, 0, -10)));
        // $shortName is for identifying cache id/namespace
        /**
         * @todo rethink this whole plan
         */
        switch ($shortName) {
            case 'languages':
            case 'locations':
            case 'event-types':
                $factory = $container->get('annotated-form-factory');
                $entityManager = $container->get('entity-manager');
                $controller = new $requestedName(
                    $entityManager,
                    $factory,
                    $shortName
                );
                break;
            case 'court-closings':
                $controller = new $requestedName(
                    $container->get('entity-manager')
                );
                break;
            default:
                throw new \RuntimeException(
                    'controller factory cannot not instantiate ' .
                    "$requestedName a/k/a $shortName"
                );
        }
        // ensure UpdateListener knows who current user is
        $container->get(Listener\UpdateListener::class)
            ->setAuth($container->get('auth'));

        return $controller;
    }
}
