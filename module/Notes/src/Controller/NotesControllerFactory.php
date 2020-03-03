<?php
/**  module/Notes/src/Controller/NotesControllerFactory.php */
namespace InterpretersOffice\Admin\Notes\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Notes\Controller\NotesController;
use InterpretersOffice\Admin\Notes\Service\NotesService;
use InterpretersOffice\Entity\Listener;
/**
 * factory for NotesController
 */
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
        $notesService = $container->get(NotesService::class);
        // attach the entity listeners -- kind of stupid?   
        $em = $container->get('entity-manager');
        $auth = $container->get('auth');
        $resolver = $em->getConfiguration()->getEntityListenerResolver();
        $resolver->register($container->get(Listener\UpdateListener::class)->setAuth($auth));

        return new NotesController($notesService);
    }
}
