<?php

namespace InterpretersOffice\Form;

use Zend\Form\Element\Csrf;
use Zend\InputFilter;

trait CsrfElementCreationTrait
{


	function addCsrfElement()
	{

		$this->add(new Csrf('csrf'));
        // customize validation error messages
        $inputFilter = $this->getInputFilter();
        $factory = new InputFilter\Factory();
        $inputFilter->merge(
            $factory->createInputFilter([
                'csrf' => [
                    'name' => 'csrf',
                    'validators' => [
                        [
                            'name' => 'Zend\Validator\NotEmpty',
                            'options' => [
                                'messages' => [
                                    'isEmpty' => 'security error: missing CSRF token',
                                ],
                            ],
                        ],
                        [
                            'name' => 'Zend\Validator\Csrf',
                            'options' => [
                                'messages' => [
                                    'notSame' => 'security error: invalid CSRF token',
                                ],
                            ],
                        ],
                    ],
                ]
            ])
        );
      	return $this;
	}
}