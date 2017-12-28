<?php
/** module/Admin/src/Form/EventForm.php */

namespace InterpretersOffice\Admin\Form;

use Zend\Form\Form as ZendForm;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Form\CsrfElementCreationTrait;

//use Zend\EventManager\ListenerAggregateInterface;
//use Zend\EventManager\ListenerAggregateTrait;
//use Zend\EventManager\EventManagerInterface;
//use Zend\EventManager\EventInterface;

use Zend\InputFilter\InputFilterProviderInterface;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use InterpretersOffice\Form\ObjectManagerAwareTrait;


/**
 * form for Event entity
 *
 */
class DefendantForm extends ZendForm implements  InputFilterProviderInterface   
{

     use CsrfElementCreationTrait;
     
     use ObjectManagerAwareTrait;

   

    /**
     * name of the form
     *
     * @var string
     */
    protected $formName = 'defendant-form';
    
   
     /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array         $options
     */
    public function __construct(ObjectManager $objectManager, $options = null)
    {
        parent::__construct($this->formName, $options);
        $this->setObjectManager($objectManager);
        $this->setHydrator(new DoctrineHydrator($objectManager, true));
        /* putting this here instead of in the fieldset and handling the logic 
         * ourself saves us some pain          
        if ("update" == $this->options['action']) {
            $this->add([
                'type'=> 'Hidden',
                'name'=> 'modified',  
                'attributes' => ['id' => 'modified'],
            ]);
        }*/
        $this->addCsrfElement();                
    }
    
    /**
     * implements InputFilterProviderInterface
     *
     * @return array
     */   
    function getInputFilterSpecification()
    {
        return [];
    }

}
