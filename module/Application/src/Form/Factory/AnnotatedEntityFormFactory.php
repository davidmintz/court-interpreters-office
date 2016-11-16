<?php

namespace Application\Form\Factory;

use Interop\Container\ContainerInterface;
use Application\Entity;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DoctrineModule\Validator\NoObjectExists as NoObjectExistsValidator;
use DoctrineModule\Validator\UniqueObject;
use Zend\Form\Form;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\Form\Annotation\AnnotationBuilder;

class AnnotatedEntityFormFactory implements FormFactoryInterface
{
    /** @var ObjectManager */
    protected $objectManager;
    
    
    function __invoke(ContainerInterface $container,$requestedName,$options = []){
        $this->objectManager = $container->get('entity-manager');
        return $this;
    }

    /**
     * creates a Zend\Form\Form
     * 
     * @param type $entity
     * @param array $options
     * @todo check $options, throw exception
     * @return Form
     */
    function createForm($entity, Array $options)
    {
        $annotationBuilder = new AnnotationBuilder();
        $form = $annotationBuilder->createForm($entity);
        switch ($entity) {
            case Entity\Language::class:
            $this->setupLanguageForm($form, $options);
            break;
            
            case Entity\Locations::class:
            $this->setupLanguageForm($form, $options);
            break;
            // etc
            
        }
        $form->setHydrator(new DoctrineHydrator($this->objectManager))
             ->setObject($options['object']);
        return $form;
    }
    /**
     * continues the initialization of the Language form.
     *
     * @param Form  $form
     * @param array $options
     */
    public function setupLanguageForm(Form $form, array $options)
    {
        $em = $this->objectManager;

        if ($options['action'] == 'create') {
            $validator = new NoObjectExistsValidator([
               'object_repository' => $em->getRepository('Application\Entity\Language'),
               'fields' => 'name',
               'messages' => [
                   NoObjectExistsValidator::ERROR_OBJECT_FOUND => 'this language is already in your database', ],
           ]);
        } else { // assume update

           $validator = new UniqueObject([
               'object_repository' => $em->getRepository('Application\Entity\Language'),
               'object_manager' => $em,
               'fields' => 'name',
               'use_context' => true,
               'messages' => [UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 
                   'language names must be unique; this one is already in your database'],
           ]);
        }
        $input = $form->getInputFilter()->get('name');
        $input->getValidatorChain()
          ->attach($validator);
    }

    public function setupLocationsForm(Form $form, Array $options)
    {

        // to be implemented
    }
}

