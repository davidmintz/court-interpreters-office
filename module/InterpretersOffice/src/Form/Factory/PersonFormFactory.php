<?php
/** module/InterpretersOffice/Form/Factory/PersonFormFactory.php */

namespace InterpretersOffice\Form\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use InterpretersOffice\Form\PersonForm;

/**
 * Factory class for PersonForm
 * 
 * @see InterpretersOffice\Form\PersonForm 
 * 
 */
class PersonFormFactory implements FactoryInterface
{

	/**
     * implements FactoryInterface.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return PersonForm
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {

    	return new PersonForm (
            $container->get('entity-manager')
        );
    }
}
