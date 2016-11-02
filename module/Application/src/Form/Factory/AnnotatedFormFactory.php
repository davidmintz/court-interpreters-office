<?php
/** module/Application/src/Form/Factory/SimpleFormFactory.php */

namespace Application\Form\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Interop\Container\ContainerInterface;

use Application\Entity;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Annotation\AnnotationBuilder;


/**
 * 
 * Factory for creating Form objects for our relatively simple 
 * entities from the entities' annotations
 *
 * 
 */
class AnnotatedFormFactory implements FactoryInterface {
    public function __invoke(ContainerInterface $container, $requestedName, Array $options = null)
    {
        switch ($requestedName) {
            case 'language':
                $entityName = Entity\Language::class;
                
        }
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($entityName);
        $form->setHydrator(new DoctrineHydrator($container->get('entity-manager')))
             ->setObject($options['object']);
        return $form;
    }
    //public function __construct() { echo "constructor";}
}
