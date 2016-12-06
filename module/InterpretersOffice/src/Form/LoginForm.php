<?php 
namespace InterpretersOffice\Form;

use Zend\Form\Form;


use Zend\InputFilter\InputFilterProviderInterface;

class LoginForm extends Form implements InputFilterProviderInterface
{



	public function __construct($options = null)
	{
		
		parent::__construct($options);
		///*
		$this->add(
		[
            'type' => 'Zend\Form\Element\Text',
            'name' => 'identity',
            'options' => [
                'label' => 'username or email',
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'username or email',
                'id' => 'identity',
            ],
        ]
		);
		//*/
		$this->add(
		[
            'type' => 'Zend\Form\Element\Password',
            'name' => 'password',
            'options' => [
                'label' => 'password',
            ],
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'password',
                'id' => 'password'
            ],
        ]
		);
	}
	public function getInputFilterSpecification()
	{
		return[
            
            'identity' =>[
                'validators' => [
                    [
                      'name' => 'NotEmpty',
                      'options' =>[
                           'messages' => [\Zend\Validator\NotEmpty::IS_EMPTY => 'identity is required']
                        ],
                    ],
                ],
                'filters' => [
                    [
                        'name' => 'StringTrim',
                    ]
                ],
            ],
            'password' => [
                'validators' => [
                    [
                      'name' => 'NotEmpty',
                       'options' =>[
                           'messages' => [\Zend\Validator\NotEmpty::IS_EMPTY => 'password is required']
                        ]
                    ],
                ],
                'filters' => [
                    [
                       'name' => 'StringTrim',
                    ]
                ],
            ]
        ];
	}



}