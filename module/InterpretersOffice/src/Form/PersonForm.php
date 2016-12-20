<?php 

namespace InterpretersOffice\Form;

use Zend\Form\Form as ZendForm;
use Zend\Form\Element\Csrf;
//use Zend\InputFilter\InputFilterProviderInterface;
//use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

class PersonForm extends ZendForm //implements ObjectManagerAwareInterface
{
	//use ObjectManagerAwareTrait;
    
    protected $formName = 'person-form';
    
	public function __construct(ObjectManager $objectManager,$options = null)
	{
			
		parent::__construct($this->formName,$options);
		$fieldset = new PersonFieldset($objectManager);
		$this->add($fieldset);
        $this->add(new Csrf('csrf'));
	}



}
