<?php /** module/Admin/src/Form/InterpretersAssignedFieldset.php */
namespace InterpretersOffice\Admin\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use InterpretersOffice\Form\ObjectManagerAwareTrait;
use InterpretersOffice\Entity;

/**
 * fieldset for Interpreters assigned to an Event
 */
class InterpretersAssignedFieldset extends Fieldset 
    implements InputFilterProviderInterface
{
    use ObjectManagerAwareTrait;
    
    /**
     * constructor
     * 
     * @param ObjectManager $objectManager
     * @param array $options
     */
    public function __construct(ObjectManager $objectManager, Array $options = [])
    {

        parent::__construct('interpretersAssigned', $options);
        $this->objectManager = $objectManager;
        $this->setHydrator(new DoctrineHydrator($objectManager));
        $this->setObject(new Entity\InterpreterEvent());
        $this->options = $options;
        
        $this->add(
            [
                'type'=>'hidden',
                'name' => 'interpreter'
            ]
        );
    }
    
    /**
     * implements InputFilterProviderInterface
     * 
     * @todo complete it
     * @return array
     */
    public function getInputFilterSpecification() {
        return [];
    }
    
}

