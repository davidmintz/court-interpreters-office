<?php
/**  module/Notes/src/Service/NotesServiceFactory.php */
namespace InterpretersOffice\Admin\Notes\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Notes\Service\NotesService;

/** factory for NotesService */
class NotesServiceFactory implements FactoryInterface
{
    /**
     * instantiates NotesService
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return NotesService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new NotesService(
            $container->get('entity-manager'),
            $container->get('auth')
        );
    }
}
