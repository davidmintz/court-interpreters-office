<?php
namespace InterpretersOffice\Admin\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use InterpretersOffice\Form\ObjectManagerAwareTrait;
use InterpretersOffice\Entity;

class InterpretersAssignedFieldset extends Fieldset 
    implements InputFilterProviderInterface
{
    use ObjectManagerAwareTrait;
    
    public function __construct(ObjectManager $objectManager, Array $options = [])
    {

        parent::__construct('interpretersAssigned', $options);
        $this->objectManager = $objectManager;
        $this->setHydrator(new DoctrineHydrator($objectManager));
        $this->setObject(new Entity\InterpreterEvent());
        $this->options = $options;
        
        $this->add(
            [
                'type'=>'text',
                'name' => 'interpreter'
            ]
        );
    }
    
    
    public function getInputFilterSpecification() {
        return [];
    }
    
}

