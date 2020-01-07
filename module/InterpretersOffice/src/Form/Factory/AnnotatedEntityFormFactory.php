<?php

/** module/InterpretersOffice/src/Form/Factory/AnnotatedEntityFormFactory.php */

namespace InterpretersOffice\Form\Factory;

use Interop\Container\ContainerInterface;
use InterpretersOffice\Entity;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use InterpretersOffice\Form\Validator\NoObjectExists as NoObjectExistsValidator;
use InterpretersOffice\Form\Validator\UniqueObject;
//use DoctrineModule\Validator\UniqueObject;

use Laminas\Form\Form;
use Doctrine\Common\Persistence\ObjectManager;
use Laminas\Form\Annotation\AnnotationBuilder;
use InterpretersOffice\Form\Validator\ParentLocation as ParentLocationValidator;
use Laminas\InputFilter; //\Input;
use Laminas\Form\Element\Csrf;

/**
 * Factory for creating forms for the entities that are relatively simple.
 *
 * Those entities' properties have some of their corresponding form elements
 * defined via annotations. This factory sets up the remaining elements that
 * cannnot be set up via annotations, or whose complete configuration can't be
 * decided until the action is dispatched.
 */
class AnnotatedEntityFormFactory implements FormFactoryInterface
{
    /**
     * Doctrine object manager instance.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * invocation, if you will.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return \InterpretersOffice\Form\Factory\AnnotatedEntityFormFactory
     */
    public function __invoke(ContainerInterface $container, $requestedName, $options = [])
    {
        $this->objectManager = $container->get('entity-manager');

        return $this;
    }

    /**
     * creates a Laminas\Form\Form.
     *
     * factory method to instantiate and, if needed, complete initialization
     * of a Form for creating or updating the entity
     *
     * @param string $entity
     * @param array  $options
     *
     * @todo check $options, throw exception
     *
     * @return Form
     */
    public function createForm($entity, array $options)
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

            case Entity\EventType::class:
                $this->setupEventTypesForm($form, $options);
                break;
        }
        $form->setHydrator(new DoctrineHydrator($this->objectManager))
             ->setObject($options['object']);

        // add the CSRF element to the form
        $form->add(new Csrf('csrf'));
        // and customize its validation error messages
        $inputFilter = $form->getInputFilter();
        $factory = new InputFilter\Factory();
        $inputFilter->merge(
            $factory->createInputFilter([
                'csrf' => [
                    'name' => 'csrf',
                    'validators' => [
                        [
                            'name' => 'Laminas\Validator\NotEmpty',
                            'options' => [
                                'messages' => [
                                    'isEmpty' => 'security error: missing CSRF token',
                                ],
                            ],
                        ],
                        [
                            'name' => 'Laminas\Validator\Csrf',
                            'options' => [
                                'messages' => [
                                    'notSame' => 'security error: invalid CSRF token',
                                ],
                            ],
                        ],
                    ],
                ],
            ])
        );

        return $form;
    }

    /**
     * completes the initialization of the EventType create|update form.
     *
     * @param Form  $form
     * @param array $options
     */
    public function setupEventTypesForm(Form $form, array $options)
    {
        /// https://github.com/doctrine/DoctrineModule/issues/252
        /// https://kuldeep15.wordpress.com/2015/04/08/composite-key-type-duplicate-key-check-with-zf2-doctrine/

        $validatorOptions = [
            'object_repository' => $this->objectManager->getRepository('InterpretersOffice\Entity\EventType'),
            'fields' => 'name',
            'object_manager' => $this->objectManager,
            'use_context' => true,
        ];
        if ($options['action'] == 'create') {
            $validatorClass = NoObjectExistsValidator::class;
            $validatorOptions['messages'] = [
                NoObjectExistsValidator::ERROR_OBJECT_FOUND => 'this event-type is already in your database',
            ];
        } else { // presume update
            $validatorClass = UniqueObject::class;
            $validatorOptions['messages'] = [
                UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'another event-type by this name is already in your database',
            ];
        }
        $uniquenessValidator = new $validatorClass($validatorOptions);
        $nameInput = $input = $form->getInputFilter()->get('name');
        $nameInput->getValidatorChain()->attach($uniquenessValidator, true);
    }

    /**
     * completes the initialization of the Language create|update form.
     *
     * @param Form  $form
     * @param array $options
     */
    public function setupLanguageForm(Form $form, array $options)
    {
        $validatorOptions = [
            'object_repository' => $this->objectManager->getRepository('InterpretersOffice\Entity\Language'),
            'fields' => 'name',
            'object_manager' => $this->objectManager,
            'use_context' => true,
        ];
        if ($options['action'] == 'create') {
            $validatorClass = NoObjectExistsValidator::class;
            $validatorOptions['messages'] = [
                   NoObjectExistsValidator::ERROR_OBJECT_FOUND => 'this language is already in your database', ];
        } else { // assume update
            $validatorClass = UniqueObject::class;
            $validatorOptions['messages'] = [
                UniqueObject::ERROR_OBJECT_NOT_UNIQUE => 'language names must be unique; this one is already in your database',
            ];
        }
        $input = $form->getInputFilter()->get('name');
        $input->getValidatorChain()->attach(new $validatorClass($validatorOptions));
    }

    /**
     * completes initialization of the Locations create|update form.
     *
     * @param Form  $form
     * @param array $options
     */
    public function setupLocationsForm(Form $form, array $options)
    {
        // first, a LocationsType drop down menu

        // file:///opt/www/court-interpreters-office/vendor/doctrine/doctrine-module/docs/form-element.md
        // for how to add html attributes to options
        $context = key_exists('form_context', $options) ? $options['form_context'] : null;
        $parentLocationOptions = [];
        $locationTypeOptions = [];
        if ('judges' == $context) {
            $parentLocationOptions['find_method'] = 'getCourthouses';
            $locationTypeOptions['find_method'] = 'getJudgeLocationsTypes';
        } else {
            $parentLocationOptions['find_method'] = 'getParentLocations';
            $locationTypeOptions['find_method'] = null;
        }
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
                'empty_item_label' => '(none)',

                'find_method' => ['name' => $parentLocationOptions['find_method']],
                'option_attributes' => [
                    'data-location-type' => function (Entity\Location $location) {
                        return $location->getType();
                    },
                ],
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
            'required' => true,
            'options' => [
                'object_manager' => $this->objectManager,
                'target_class' => 'InterpretersOffice\Entity\LocationType',
                'property' => 'type',
                'label' => 'location type',
                'display_empty_item' => true,
                'find_method' => $locationTypeOptions['find_method'] ?
                    ['name' => $locationTypeOptions['find_method']] : null,
                'empty_item_label' => '(required)',
            ],
             'attributes' => [
                'class' => 'form-control',
                'id' => 'type',

             ],
        ]);
        $filter = $form->getInputFilter();
        // location type is required
        $input = new InputFilter\Input('type');
        /** @todo just pass an array to the input filter? */
        $notEmptyValidator = new \Laminas\Validator\NotEmpty([
            'messages' => [
                'isEmpty' => 'location type is required',
            ],
        ]);
        // enforce rules as to location and parent locations
        $locationValidator = new ParentLocationValidator([
            'parentLocations' => $form->get('parentLocation')->getValueOptions(),
            'locationTypes' => $form->get('type')->getValueOptions(),
        ]);
        $input->getValidatorChain()
            ->attach($notEmptyValidator, true)
            ->attach($locationValidator);

        $filter->add($input);

        /// https://github.com/doctrine/DoctrineModule/issues/252
        /// https://kuldeep15.wordpress.com/2015/04/08/composite-key-type-duplicate-key-check-with-zf2-doctrine/

        $validatorOptions = [
               'object_repository' => $this->objectManager
                   ->getRepository('InterpretersOffice\Entity\Location'),
               'fields' => ['name', 'parentLocation'],
               'object_manager' => $this->objectManager,
               'use_context' => true, ];

        if ($options['action'] == 'create') {
            $validatorClass = NoObjectExistsValidator::class;
            $validatorOptions['messages'] = [
                NoObjectExistsValidator::ERROR_OBJECT_FOUND =>
                    'this location is already in your database',
            ];
        } else { // assume this is an update
            $validatorClass = UniqueObject::class;
            $validatorOptions['messages'] =
              [UniqueObject::ERROR_OBJECT_NOT_UNIQUE =>
              'there is already an existing location with this name and parent location'];
        }

        $nameInput = $filter->get('name');
        $nameInput->getValidatorChain()
                ->attach(new $validatorClass($validatorOptions), true);

        $filter->add([
            'name' => 'parentLocation',
            'required' => true,
            'allow_empty' => true,
        ]);
    }
}
