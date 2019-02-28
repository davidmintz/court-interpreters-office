<?php
/**
 * module/InterpretersOffice/src/Form/InterpreterLanguageFieldset.php.
 */

namespace InterpretersOffice\Admin\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use InterpretersOffice\Form\ObjectManagerAwareTrait;
use InterpretersOffice\Entity;

/**
 * Fieldset for Interpreter's working languages.
 *
 */
class InterpreterLanguageFieldset extends Fieldset implements InputFilterProviderInterface, ObjectManagerAwareInterface
{
    use ObjectManagerAwareTrait;

    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array         $options
     */
    public function __construct(ObjectManager $objectManager, $options = [])
    {
        parent::__construct('interpreterLanguages', $options);
        $this->objectManager = $objectManager;
        $this->setHydrator(new DoctrineHydrator($objectManager));
        $this->setObject(new Entity\InterpreterLanguage());

        $this->add([
            'name' => 'language',
            'type' => 'hidden',
        ]);
        $credential_options = $objectManager->getRepository(Entity\Language::class)
            ->getCredentialOptions();
        $this->add([
            'name' => 'languageCredential',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'value_options' =>[''=>'']+$credential_options,
            ],
            'attributes' => [
                'class' => 'form-control'
            ]

        ]);
    }

    /**
     * implements InputFilterProviderInterface.
     * @todo rethink how to validate, without hard-coding rules based on the
     * federal court classification system.
     * @return array
     */
    public function getInputFilterSpecification()
    {
        //exit(sprintf("nice to know %s was called<br>\n",__METHOD__));
        $repository = $this->getObjectManager()->getRepository(Entity\Language::class);
        $certified_languages = $repository->findAllCertifiedLanguages();
        $spec = [
            'languageCredential' => [
                'required' => true,
                'allow_empty' => true,
                /*'filters' => [
                    [   // this callback runs twice per validator unless it
                        // returns null, in which case validator callback
                        // runs NEVER. not helpful.
                        'name' => 'Zend\Filter\Callback',
                        'options' => [
                            'callback' => function ($value) {
                               //echo "FILTER: filtering value $value\n";
                               switch($value) {
                                   case "-1":
                                       return null;
                                   case "0" :
                                       return false;
                                   case  "1" :
                                       return true;
                                   default:
                                     //  return $value;

                               }
                            },
                        ],
                    ],
                ],*/
                /** @todo two callback validators w/ different error msg
                 * for each case: certified or non-certified language*/
                'validators' => [
                    // [
                    //     'name' => 'Callback',
                    //     'options' => [
                    //         'callback' => function ($value, $context) use ($certified_languages) {
                    //             // echo "is this shit even running at all in ".basename(__FILE__). " ? ....";
                    //             //echo "VALIDATOR: value is $value, context is \n".print_r($context,true);
                    //             $certified_language_ids = array_keys($certified_languages);
                    //             //echo "certified languages ids are \n".print_r($certified_language_ids,true);
                    //             $is_a_certified_language = in_array($context['language'], $certified_language_ids);
                    //             //echo "language id is $context[language] ...";
                    //             if ($is_a_certified_language && in_array($value, ["0","1"])) {
                    //                 //echo "value $value, returning true for certified...\n";
                    //                 return true;
                    //             }
                    //             if ((! $is_a_certified_language) && $value == "-1") {
                    //                 //echo "value $value, returning true for non-certified...\n";
                    //                 return true;
                    //             }
                    //             //echo "value $value, returning false...\n";
                    //             return false;
                    //         },
                    //         'messages' => [
                    //             \Zend\Validator\Callback::INVALID_VALUE
                    //             => 'yes or no is required for certified languages',
                    //         ],
                    //     ],
                    // ],
                ]
            ]
        ];


        $spec['language'] = [
                'required' => true,
                'allow_empty' => false,
                // objectexists validator ?
        ];
        //print_r($spec);
        return $spec;
    }
}
