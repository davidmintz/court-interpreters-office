<?php

namespace InterpretersOffice\Admin\Form;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Form\EventForm;
use InterpretersOffice\Entity;
use function file_get_contents;
use function json_decode;
use function is_readable;

class EventFormFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return EventForm
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $params = $container->get('Application')->getMvcEvent()
            ->getRouteMatch()->getParams();
        $em = $container->get('entity-manager');
        /** get form config options (optional fields) */
        $config_path = 'module/Admin/config/forms.json';
        $options = [];
        if (is_readable($config_path)) {
            $json = file_get_contents($config_path);
            $config = json_decode($json, \JSON_OBJECT_AS_ARRAY);
            if (isset($config['events']) && isset($config['events']['optional_elements'])) {
                $options['optional_elements'] = $config['events']['optional_elements'];
            }
        }
        if (! $options) {
            $container->get('log')->warn("EventFormFactory: unable to load events form configuration file");
        }
        if ('edit' == $params['action']) {
            $id = $params['id'];
            /** @var \InterpretersOffice\Entity\Event $entity  */
            $entity = $em->getRepository(Entity\Event::class)
                ->load($id);
            // echo "entity id is $id ...";die(get_class($entity));
            $options += ['object' => $entity, 'action' => 'update',];
            $form = new EventForm($em, $options);
            $form->setObject($entity ?? new Entity\Event());
        } else {
            $form = new EventForm($em, $options + ['object' => null, 'action' => 'create',]);
        }

        return $form;
    }
}
