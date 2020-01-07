<?php /** module/Notes/src/Service/NotesServiceFactory.php */
namespace InterpretersOffice\Admin\Rotation\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Rotation\Service\TaskRotationService;

/**
 * TaskRotationServiceFactory
 */
class TaskRotationServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return TaskRotationService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new TaskRotationService(
            $container->get('entity-manager'),
            $container->get('config')['rotation'] ?? []
        );
    }
}
