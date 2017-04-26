<?php
/**  Interpreter search UI */

namespace InterpretersOffice\Admin\Form;

use Zend\Form\Form;
use InterpretersOffice\Form\Element\LanguageSelect;
use Zend\Form\Element;
/**
* 
*/
class InterpreterRosterForm extends Form
{
	
	function __construct($options)
	{
		parent::__construct($options);

		$this->add(
			new LanguageSelect(
   				'language-select',
                ['objectManager' => $options['objectManager'],]
			)
		);
        
        $this->add(
             [
                 'type' => 'Zend\Form\Element\Select',
                 'name' => 'active',
                 'value_options' => [
                      1 => 'active',
                      0 => 'not active',
                     -1 => 'any status',
                 ],
                 'options' => [
                     'id' => 'active',
                     'class' => 'form-control',
                 ]                 
             ]
        );
	}
}