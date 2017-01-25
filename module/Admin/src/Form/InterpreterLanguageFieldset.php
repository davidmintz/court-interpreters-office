<?php
/**
 * module/InterpretersOffice/src/Form/InterpreterLanguageFieldset.php
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
 * Fieldset for Interpreter's working languages
 */
class InterpreterLanguageFieldset extends Fieldset implements InputFilterProviderInterface, ObjectManagerAwareInterface
{
    
    use ObjectManagerAwareTrait;
    
    public function __construct(ObjectManager $objectManager, $options = []) {
        
        parent::__construct('interpreterLanguages', $options);
        $this->objectManager = $objectManager;
        $this->setHydrator(new DoctrineHydrator($objectManager)); 
        $this->setObject(new Entity\InterpreterLanguage);
        /*
        $this->add([
            'name' => 'interpreter',
            'type' => 'hidden',             
        ]);
        */
        $this->add([
            'name' => 'language',
            'type' => 'hidden',            
        ]);

        $this->add([
            'name' => 'federalCertification',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'value_options' => [
                    '' => 'N/A',
                    1 => 'yes',
                    0 => 'no',
                ],
            ],
            
        ]);
    }

    public function getInputFilterSpecification()
    {
        echo __METHOD__, " is running ...";
        return [
            'federalCertification'=> [
                'required' => true,
                'allow_empty' => true,
                'filters' => [
                    [/** note to self: check if ZF provides a filter for this */
                        'name' => 'Zend\Filter\Callback',
                        'options' => [
                            'callback' => function($value) {
                                echo "SHIT IS RUNNING.";
                                switch ($value) {
                                    case "":
                                        $value = null;
                                        break;
                                    case "1":
                                        $value = true;
                                        break;
                                    case "0";
                                        $value = false;
                                        break;
                                }
                                return $value;
                            },
                        ]
                    ]
                ],
            ],
            'language' => [
                'required' => true,
                'allow_empty' => false,               
            ],
            /*
            'interpreter' => [
                'required' => true,
                'allow_empty' => false,                
            ],             
             */
        ];
    }
}
