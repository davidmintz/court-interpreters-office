<?php

namespace InterpretersOffice\Admin\Form;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Form\EventForm;
use InterpretersOffice\Entity;

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
        $options = [];
        $em = $container->get('entity-manager');
        if ('edit' == $params['action']) {
            $id = $params['id'];
            /** @var \InterpretersOffice\Entity\Event $entity  */
            $entity = $em->getRepository(Entity\Event::class)
                ->load($id);
            $options = ['object' => $entity, 'action' => 'update',];
            $form = new EventForm($em, $options);
            $form->setObject($entity);
        } else {
            $form =  new EventForm($em, ['object' => null, 'action' => 'create',]);
        }

        return $form;

    }
}
