<?php

namespace InterpretersOffice\Form\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use InterpretersOffice\Form\PersonForm;

class PersonFormFactory implements FactoryInterface
{

	/**
     * implements FactoryInterface.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return UserListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {

    	return new PersonForm (
            
            $container->get('entity-manager')
        );

    }



}
