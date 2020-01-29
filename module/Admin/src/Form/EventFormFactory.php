<?php

namespace InterpretersOffice\Admin\Form;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use InterpretersOffice\Admin\Form\EventForm;

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
        return new EventForm($container->get('entity-manager'));
    }
}
