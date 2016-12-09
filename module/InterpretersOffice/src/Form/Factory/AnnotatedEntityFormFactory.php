<?php
/** module/InterpretersOffice/src/Form/Factory/AnnotatedEntityFormFactory.php */

namespace InterpretersOffice\Form\Factory;

use Interop\Container\ContainerInterface;
use InterpretersOffice\Entity;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

use InterpretersOffice\Form\Validator\NoObjectExists as NoObjectExistsValidator;
use InterpretersOffice\Form\Validator\UniqueObject;
//use DoctrineModule\Validator\UniqueObject;

use Zend\Form\Form;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\Form\Annotation\AnnotationBuilder;

use InterpretersOffice\Form\Validator\ParentLocation as ParentLocationValidator;

use Zend\InputFilter\Input;

/**
 * Factory for creating forms for the entities that are relatively simple.
 * 
 * Those entities' properties have some of their corresponding form elements 
 * defined via annotations. This factory sets up the remaining elements that 
 * cannnot be set up via annotations or whose complete configuration can't be 
 * decided before the action is dispatched. 
 */
class AnnotatedEntityFormFactory implements FormFactoryInterface
{
    /** 
     * Doctrine object manager instance
     * @var ObjectManager 
     */
    protected $objectManager;
    
    /**
     * invocation, if you will
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return \InterpretersOffice\Form\Factory\AnnotatedEntityFormFactory
     */
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
            
            case Entity\Location::class:
            $this->setupLocationsForm($form, $options);
            break;
            // etc
            
        }
        $form->setHydrator(new DoctrineHydrator($this->objectManager))
             ->setObject($options['object']);
        return $form;
    }
    /**
     * completes the initialization of the Language create|update form.
     *
     * @param Form  $form
     * @param array $options
     */
    public function setupLanguageForm(Form $form, array $options)
    {
        $em = $this->objectManager;

        if ($options['action'] == 'create') {
            $validator = new NoObjectExistsValidator([
               'object_repository' => $em->getRepository('InterpretersOffice\Entity\Language'),
               'fields' => 'name',
               'messages' => [
                   NoObjectExistsValidator::ERROR_OBJECT_FOUND => 'this language is already in your database', ],
           ]);
        } else { // assume update

           $validator = new UniqueObject([
               'object_repository' => $em->getRepository('InterpretersOffice\Entity\Language'),
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
    /**
     * completes initialization of the Locations create|update form
     * 
     * @param Form $form
     * @param array $options
     */
    public function setupLocationsForm(Form $form, Array $options)
    {
        // first, a LocationsType drop down menu

        // file:///opt/www/court-interpreters-office/vendor/doctrine/doctrine-module/docs/form-element.md
        // for how to add html attributes to options

        $form->add([
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'parentLocation',
            'required' => true,
            'allow_empty' => true,
            'options' => [
                'object_manager' => $this->objectManager,
                'target_class' => 'InterpretersOffice\Entity\Location',
                'property' => 'name',
                'label' => 'where this location itself is located, if applicable',
                'display_empty_item' => true,
                'empty_item_label' =>  '(none)',
               
                'find_method' => ['name' => 'getParentLocations',],
                'option_attributes' => [
                    'data-location-type' => function(Entity\Location $location)
                    {
                        return $location->getType();
                    }
                ]
            ],
             'attributes' => [
                'class' => 'form-control',
                'id' => 'parentLocation',
             ],
        ]);
        
        // add a dropdown for LocationType
                
        $form->add([
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'type',
            
            'options' => [
                'object_manager' => $this->objectManager,
                'target_class' => 'InterpretersOffice\Entity\LocationType',
                'property' => 'type',
                'label' => 'location type',
                'display_empty_item' => true,
                'empty_item_label' => '(required)',
                'required' => true,
                
            ],
             'attributes' => [
                'class' => 'form-control',
                'id' => 'type',

             ],
        ]);
        $filter = $form->getInputFilter();
        // location type is required
        $input = new Input('type');
        /** @todo just pass an array to the input filter? */
        $notEmptyValidator = new \Zend\Validator\NotEmpty([
            'messages' => [
                'isEmpty' => 'location type is required',
            ],
        ]);
        // enforce rules as to location and parent locations
        $locationValidator = new ParentLocationValidator([
            'parentLocations' => $form->get('parentLocation')->getValueOptions(),
            'locationTypes'  => $form->get('type')->getValueOptions(),
        ]);
        $input->getValidatorChain()
            ->attach($notEmptyValidator,true)
            ->attach($locationValidator);
        
        $filter->add($input);
        
        /// https://github.com/doctrine/DoctrineModule/issues/252
        /// https://kuldeep15.wordpress.com/2015/04/08/composite-key-type-duplicate-key-check-with-zf2-doctrine/
        if ($options['action'] == 'create') {
            $uniquenessValidator = new NoObjectExistsValidator([
               'object_repository' =>  $this->objectManager->getRepository('InterpretersOffice\Entity\Location'),
               'fields' => ['name','parentLocation'],
               'object_manager' => $this->objectManager,
               'use_context' => true,
               'messages' => [
                   NoObjectExistsValidator::ERROR_OBJECT_FOUND => 'this location is already in your database', ],
           ]);
        } else { // assume this is an update
       
           $uniquenessValidator = new UniqueObject([
               'object_repository' =>  $this->objectManager->getRepository('InterpretersOffice\Entity\Location'),
               'object_manager' =>  $this->objectManager,
               'fields' => ['name','parentLocation'],
               'use_context' => true,
               'messages' => [UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 
                   'there is already an existing location with the same name and parent location'],
           ]);
        }
        // ->attach($uniquenessValidator,true)
        $nameInput = $filter->get('name');
        $nameInput->getValidatorChain()->attach($uniquenessValidator,true);
        
        $input->getValidatorChain()
            ->attach($notEmptyValidator,true)
            ->attach($locationValidator,true);
        
        //////
        $filter->add([
            'name' => 'parentLocation',
            'required' => true,
            'allow_empty' => true,
           
        ]); 
        
    }
}

