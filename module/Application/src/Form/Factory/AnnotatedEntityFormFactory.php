<?php
/** module/Application/src/Form/Factory/SimpleFormFactory.php */

namespace Application\Form\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Interop\Container\ContainerInterface;

use Application\Entity;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

use DoctrineModule\Validator\NoObjectExists as NoObjectExistsValidator;
use DoctrineModule\Validator\UniqueObject;
use Zend\Form\Form;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\Form\Annotation\AnnotationBuilder;

/**
 * Factory for creating Form objects for our relatively simple 
 * entities from their annotations.
 * 
 * This also completes the initialization that can't be done via annotations or 
 * until we know whether the action is create or update.
 */

class AnnotatedEntityFormFactory implements FactoryInterface
{
    
    /** 
     * @var ObjectManager the object manager
     */
    protected $objectManager;
    
    /**
     * {@inheritdoc}
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options 
     * required keys: 
     *  'action' => string create|update, 
     *  'object' => entityInstance
     * @return Form
     */
    public function __invoke(ContainerInterface $container, $requestedName, Array $options = null)
    {
        if (! class_exists($requestedName)) {
            throw new \DomainException(
                 'Factory cannot build a form from %s: class not found'
            );
        }
        $this->objectManager = $container->get('entity-manager');
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($requestedName);
        
        switch ($requestedName) {
            case Entity\Language::class:
                $this->setupLanguageForm($form, $options);
           // to be continued
        }
        $form->setHydrator(new DoctrineHydrator($this->objectManager))
             ->setObject($options['object']);
        return $form;
         
         
    }
    /**
     * continues the initialization of the Language form
     * 
     * @param Form $form
     * @param array $options
     */
    function setupLanguageForm(Form $form,Array $options)
    {
        $em = $this->objectManager;
        
        if ($options['action']=='create') {
            $validator = new NoObjectExistsValidator([
               'object_repository' => $em->getRepository('Application\Entity\Language'),
               'fields'            => 'name',
               'messages' =>[
                   NoObjectExistsValidator::ERROR_OBJECT_FOUND => 
                    'this language is already in your database'],
           ]);
       } else { // assume update
          
           $validator = new UniqueObject([
               'object_repository' => $em->getRepository('Application\Entity\Language'),
               'object_manager' => $em,
               'fields'        => 'name',
               'use_context' => true,
               'messages' =>[ UniqueObject::ERROR_OBJECT_NOT_UNIQUE =>
                   'language names must be unique; this is already in your database'],
           ]);
       }
       $input = $form->getInputFilter()->get('name');
           $input->getValidatorChain()
          ->attach($validator);
    }
}
