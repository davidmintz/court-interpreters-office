<?php
/**  Interpreter search UI */

namespace InterpretersOffice\Admin\Form;

use Zend\Form\Form;

/**
* 
*/
class InterpreterRosterForm extends Form
{
	
	function __construct($options)
	{
		
		$objectManager = $options['objectManager'];


		parent::__construct($options);

		$this->add(
			new \InterpretersOffice\Form\Element\LanguageSelect(
   				 'language-select',['objectManager' => $this->objectManager,]
			)
		);
	}
}