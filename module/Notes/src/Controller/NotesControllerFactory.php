<?php

namespace InterpretersOffice\Admin\Notes\Controller;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Notes\Controller\NotesController;

class NotesControllerFactory implements FactoryInterface
{
    /**
     * instantiates the controller
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return NotesController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new NotesController($container->get('entity-manager'));
    }
}
