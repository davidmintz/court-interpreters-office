<?php
/**
 * module/InterpretersOffice/src/Form/InterpreterLanguageFieldset.php
 */

namespace InterpretersOffice\Admin\Form;


use Zend\Form\Fieldset;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

use Zend\InputFilter\InputFilterProviderInterface;

use InterpretersOffice\Form\ObjectManagerAwareTrait;

use InterpretersOffice\Entity;


/**
 * Description of InterpreterLanguageFieldset
 *
 * @author david
 */
class InterpreterLanguageFieldset extends Fieldset implements InputFilterProviderInterface, ObjectManagerAwareInterface
{
    
    
    use ObjectManagerAwareTrait;
    
    public function __construct(ObjectManager $objectManager, $options = []) {
        
        parent::__construct('interpreterLanguages', $options);
        $this->objectManager = $objectManager;
        $this->setHydrator(new DoctrineHydrator($objectManager));  // false ?
        $this->setObject(new Entity\InterpreterLanguage);
        
        // try this and see how far we get
        
        $this->add([
             
            'name' => 'interpreter',
            'type' => 'hidden',             
        ]);
        
        $this->add([
            'name' => 'language',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'options' => [
                'object_manager' => $this->objectManager,
                'target_class' => 'InterpretersOffice\Entity\Language',
                'property' => 'name',
                'label' => 'language',
                'display_empty_item' => true,
                'empty_item_label' => '',
                

            ],
             'attributes' => [
                'class' => 'form-control',
                
             ],
            
        ]);
        
    }

    public function getInputFilterSpecification()
    {
        return [];
    }
    
    
}
