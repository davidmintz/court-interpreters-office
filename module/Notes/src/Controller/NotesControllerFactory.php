<?php

namespace InterpretersOffice\Admin\Notes\Controller;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Notes\Controller\NotesController;
use InterpretersOffice\Admin\Notes\Service\NotesService;

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
        /** consider a factory for this? */
        $notesService = new NotesService(
            $container->get('entity-manager'),
            $container->get('auth')
        );

        return new NotesController($notesService);
    }
}
