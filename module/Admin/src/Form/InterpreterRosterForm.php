<?php
/**  Interpreter search UI */

namespace InterpretersOffice\Admin\Form;

use Zend\Form\Form;
use InterpretersOffice\Form\Element\LanguageSelect;
//use Zend\Form\Element;

/**
* 
*/
class InterpreterRosterForm extends Form
{
	
	public function __construct(Array $options = [])
	{
		parent::__construct('interpreter-roster',$options);
        
		$this->add(
			new LanguageSelect(
   				'language-select',
                [
                    'objectManager' => $options['objectManager'],
                ]
			)
		);
        
        $this->add(
             [
                 'type' => 'Zend\Form\Element\Select',
                 'name' => 'active',
                 'options' => [
                    'value_options' => [
                        1 => 'active',
                        0 => 'not active',
                       -1 => 'any status',
                    ],
                 ],
                 
                 'attributes' => [
                     'id' => 'active',
                     'class' => 'form-control',
                 ]                 
             ]
        );

        $this->add(
             [
                 'type' => 'Zend\Form\Element\Select',
                 'name' => 'security_clearance_expiration',
                 'options' => [
                    'value_options' => [
                         1 => 'valid',
                         0 => 'expired',
                        -2 => 'none',
                        -1 => 'any status',
                    ],    
                 ],
                 'attributes' => [
                     'id' => 'security_clearance_expiration',
                     'class' => 'form-control',
                 ]                 
             ]
        );
        $this->add(
             [
                 'type' => 'Zend\Form\Element\Text',
                 'name' => 'name',
                 'attributes' => [
                     'id' => 'name',
                     'class' => 'form-control',
                 ]                 
             ]
        );
        
	}
}
