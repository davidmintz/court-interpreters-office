<?php /**  module/Admin/src/Controller/Factory/ScheduleControllerFactory.php */

namespace InterpretersOffice\Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Controller\ScheduleController;

/**
 * ScheduleControllerFactory
 */
class ScheduleControllerFactory implements FactoryInterface
{
    /**
     * implements Laminas\ServiceManager\FactoryInterface
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ScheduleController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config_path = 'module/Admin/config/forms.json';
        $end_time_enabled = false;
        if (is_readable($config_path)) {
            $json = file_get_contents($config_path);
            $config = json_decode($json, \JSON_OBJECT_AS_ARRAY);
            if (isset($config['events']) && isset($config['events']['optional_elements'])) {
                $options['optional_elements'] = $config['events']['optional_elements'];
                if (!empty($options['optional_elements']['end_time'])) {
                    $end_time_enabled = true;
                }
            }
        }
        return new ScheduleController($container->get('entity-manager'),['end_time_enabled'=>$end_time_enabled]);
    }
}
