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
        $rotation_cfg = $container->get('config')['rotation'] ?? [];
        $service = new NotesService(
            $container->get('entity-manager'),
            $container->get('auth'),
            $rotation_cfg
        );
        if ($rotation_cfg
            && isset($rotation_cfg['display_rotating_assignments'])) {
                $service->setIncludeTaskRotation(true);
        }
        //$container->get('log')->debug(sprintf('NotesService has options: %s',print_r($service->getOptions(),true)));

        return $service;
    }
}
