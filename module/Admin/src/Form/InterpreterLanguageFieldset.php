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

        $this->add([
            'name' => 'federalCertification',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'value_options' => [
                   -1 => 'N/A',
                    1 => 'yes',
                    0 => 'no',
                ],
                //'disable_inarray_validator'=> true,
            ],
            'attributes' => [
                'class' => 'form-control'
            ]

        ]);
    }

    /**
     * implements InputFilterProviderInterface.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        $repository = $this->getObjectManager()->getRepository(Entity\Language::class);
        $certified_languages = $repository->findAllCertifiedLanguages();
        $spec = [
            'federalCertification' => [
                'required' => true,
                'allow_empty' => true,
                /*'filters' => [
                    [   // this fucks us up. don't know why. callback runs twice
                        // per validator unless it returns null, in which case 
                        // validator callback runs NEVER 
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
                /** @todo add another callback w/ different error msg */
                'validators' => [
                    [
                        'name' => 'Callback',
                        'options' => [
                            'callback' => function($value,$context) use ($certified_languages) {
                                
                                //echo "VALIDATOR: value is $value, context is \n".print_r($context,true);
                                $certified_language_ids = array_keys($certified_languages);
                                //echo "certified languages ids are \n".print_r($certified_language_ids,true);                               
                                $is_a_certified_language = in_array($context['language'],$certified_language_ids);
                                //echo "language id is $context[language] ...";
                                if ($is_a_certified_language && in_array($value,["0","1"])) {
                                    echo "value $value, returning true for certified...\n";
                                    return true;
                                }
                                if ( (! $is_a_certified_language) && $value == "-1") {
                                    //echo "value $value, returning true for non-certified...\n";
                                    return true;
                                }
                                //echo "value $value, returning false...\n";
                                return false;
                            },
                            'messages' => [
                                \Zend\Validator\Callback::INVALID_VALUE
                                => 'yes or no is required exclusively for certified languages',
                            ],
                        ],
                    ],
                ]
            ]
        ];
        
        
        $spec['language'] =  [
                'required' => true,
                'allow_empty' => false,
                // objectexists validator ?                      
        ];
        //print_r($spec);
        return $spec;
    }
}
