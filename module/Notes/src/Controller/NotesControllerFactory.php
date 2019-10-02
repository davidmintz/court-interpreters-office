<?php
/**  module/Notes/src/Controller/NotesControllerFactory.php */
namespace InterpretersOffice\Admin\Notes\Controller;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Notes\Controller\NotesController;
use InterpretersOffice\Admin\Notes\Service\NotesService;

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

        return new NotesController($notesService);
    }
}
