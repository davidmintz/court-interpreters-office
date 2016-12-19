<?php 

namespace InterpretersOffice\Form;

use Zend\Form\Form as ZendForm;
//use Zend\InputFilter\InputFilterProviderInterface;
//use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

class PersonForm extends ZendForm //implements ObjectManagerAwareInterface
{
	//use ObjectManagerAwareTrait;

	public function __construct(ObjectManager $objectManager,$options = null)
	{
			
		parent::__construct('person-form',$options);
		$fieldset = new PersonFieldset($objectManager);
		$this->add($fieldset);

	}



}
