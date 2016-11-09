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

class AlternativeAnnotatedEntityFormFactory implements FormFactoryInterface
{
    /** @var ObjectManager */
    protected $objectManager;
    
    
    function __invoke(ContainerInterface $container,$requestedName,$options = []){
        $this->objectManager = $container->get('entity-manager');
        return $this;
    }
    /**
     * 
     * @param type $entity
     * @param array $options
     * @return Form
     */
    function createForm($entity, Array $options)
    {
        $annotationBuilder = new AnnotationBuilder();
        $form = $annotationBuilder->createForm($entity);
        switch ($entity) {
            case Entity\Language::class:
                $this->setupLanguageForm($form, $options);
            // etc
            
        }
        $form->setHydrator(new DoctrineHydrator($this->objectManager))
             ->setObject($options['object']);
        echo "returning ",get_class($form);
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
}

