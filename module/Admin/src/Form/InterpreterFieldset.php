<?php
/**
 * module/Admin/src/Form/InterpreterFieldset.php.
 */

namespace InterpretersOffice\Admin\Form;

use InterpretersOffice\Form\PersonFieldset;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Entity;

// experimental
use Zend\Form\Element;

/**
 * InterpreterFieldset.
 *
 * @author david
 */
class InterpreterFieldset extends PersonFieldset
{
    /**
     * name of the fieldset.
     *
     * since we are a subclass of PersonFieldset, this needs to be overriden
     *
     * @var string
     */
    protected $fieldset_name = 'interpreter';

    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array         $options
     */
    public function __construct(ObjectManager $objectManager, $options = [])
    {
        parent::__construct($objectManager, $options);
        /*
        // could not get this to hydrate properly, so we're not using
        // Element\Collection with a InterpreterLanguageFieldset
        $this->add([
            'type' => Element\Collection::class,
            'name' => 'interpreterLanguages',
            'options' => [
                'label' => 'working languages',
               // 'count' => 2,
                'should_create_template' => false,
                'allow_add' => true,
                'allow_remove' => true,
                'target_element' => new InterpreterLanguageFieldset($objectManager),
            ],
        ]);
        */
        $this->add(
            new \InterpretersOffice\Form\Element\LanguageSelect(
                'language-select',
                [
                    'objectManager' => $objectManager,
                    'option_attributes' => ['data-certifiable' => function (Entity\Language $language) {
                        return $language->isFederallyCertified() ? 1 : 0;
                    }],
                ]
            )
        );
/*      // this might get taken out if all goes well
        $this->add([
            //'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'type' => 'InterpretersOffice\Form\Element\LanguageSelect',
            'name' => 'language-select',
            'options' => [
                'object_manager' => $this->objectManager,
                //'target_class' => 'InterpretersOffice\Entity\Language',
                //'property' => 'name',
                //'find_method' => ['name' => 'getInterpreterHats'],
                //'label' => 'languages',
                'option_attributes' => ['data-certifiable' => function (Entity\Language $language) {
                    return $language->isFederallyCertified() ? 1 : 0;
                }],
            ],
             'attributes' => [
                'class' => 'form-control',
                'id' => 'language-select',
             ],
        ]);
        $element = $this->get('language-select');
        $options = $element->getValueOptions();
        array_unshift($options, [
            'label' => '-- select a language --',
            'value' => '',
            'attributes' => ['label' => ' '],
        ]);
        $element->setValueOptions($options);
*/
        $this->add([
            'type' => 'Select',
            'name' => 'interpreter-languages',

            'attributes' => [
                'multiple' => 'multiple',
                'class' => 'hidden',
            ],
            'options' => [
                'value_options' => [],
                'disable_inarray_validator' => true,
                'use_hidden_element' => true,
            ],
        ]);
    }
    /**
     * adds the specialized "Hat" element to the form.
     *
     * @return \InterpretersOffice\Admin\Form\InterpreterFieldset
     */
    public function addHatElement()
    {
        $this->add([
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'hat',
            'options' => [
                'object_manager' => $this->objectManager,
                'target_class' => 'InterpretersOffice\Entity\Hat',
                'property' => 'name',
                'find_method' => ['name' => 'getInterpreterHats'],
                'label' => 'hat',
            ],
             'attributes' => [
                'class' => 'form-control',
                'id' => 'hat',
             ],
        ]);
        // hack designed to please HTML5 validator
        $element = $this->get('hat');
        $options = $element->getValueOptions();
        array_unshift($options, [
           'label' => ' ',
           'value' => '',
           'attributes' => [
               'label' => ' ',
           ],
        ]);
        $element->setValueOptions($options);

        return $this;
    }

    /**
     * overrides parent implementation of InputFilterProviderInterface.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        $spec = parent::getInputFilterSpecification();

        $language_options = $this->get('language-select')->getValueOptions();

        // require users to provide yes|no for federal-certified language
        // which we already know from the language select > option elements'
        // 'certifiable' attribute
        $certifiable = array_column($language_options, 'attributes', 'value');

        $spec['interpreter-languages'] = [

            'allow_empty' => false,
            'required' => true,
            'validators' => [
                 [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => [
                            'isEmpty' => 'at least one language is required',
                        ],
                    ],
                    'break_chain_on_failure' => true,
                 ],
                [   // backdoor method for ensuring 'federalCertification' field
                    // is set, if appropriate: ignore the $value and inspect the
                    // $context array
                    'name' => 'Callback',
                    'options' => [
                        'callback' => function ($value, $context) use ($certifiable) {
                            $languages_submitted = $context['interpreter-languages'];
                            foreach ($languages_submitted as $language) {
                                $id = $language['language_id'];
                               // should never happen unless they are messing with us
                                if (! isset($language['federalCertification'])) {
                                    return false;
                                }
                                $submitted_cert = is_numeric($language['federalCertification']) ?
                                       (bool) $language['federalCertification'] : null;
                                $cert_required = (bool) $certifiable[$id]['data-certifiable'];
                                if ($cert_required && ! is_bool($submitted_cert)) {
                                    return false;
                                }
                            }

                            return true;
                        },
                        'messages' => [
                            \Zend\Validator\Callback::INVALID_VALUE => 'yes/no required for federal certification',
                        ],
                    ],
                ],
            ],
        ];

        // this one is just for the UI, not part of the entity's data
         $spec['language-select'] = [
            'required' => true,
            'allow_empty' => true,
         ];
         $spec['hat'] = [
                'validators' => [
                    [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => [
                            'isEmpty' => 'hat is required (contract or staff)',
                        ],
                    ],
                    'break_chain_on_failure' => true,
                    ],
                ],
         ];

         return $spec;
    }
}
