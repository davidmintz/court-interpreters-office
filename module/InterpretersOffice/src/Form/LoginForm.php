<?php

/** module/InterpretersOffice/src/Form/LoginForm.php */

namespace InterpretersOffice\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * login form.
 */
class LoginForm extends Form implements InputFilterProviderInterface
{
    /**
     * constructor.
     *
     * @param array $options
     */
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
                'size' => 35,
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
                'id' => 'password',
                'size' => 35,
            ],
            ]
        );
        $csrf = new \Zend\Form\Element\Csrf('login_csrf');
        $csrf->setCsrfValidatorOptions(
            ['messages' => [
            'notSame' => 'security error: form submission failed CSRF token validation',
            ]]
        );

        $this->add($csrf);
        // really? I doubt it.
        $this->add([
            'type' => 'Hidden',
            'name' => 'referrer',
        ]);
        $inputFilter = $this->getInputFilter();
        $validatorChain = $inputFilter->get('login_csrf')->getValidatorChain();
        $validatorChain->prependByName('NotEmpty', ['messages' => [
           'isEmpty' => 'security error: missing CSRF token',
        ]], true);
    }

    /**
     * input filter specification.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return[

            'identity' => [
                'validators' => [
                    [
                      'name' => 'NotEmpty',
                      'options' => [
                           'messages' => [\Zend\Validator\NotEmpty::IS_EMPTY => 'identity is required'],
                        ],
                    ],
                ],
                'filters' => [
                    [
                        'name' => 'StringTrim',
                    ],
                ],
            ],
            'password' => [
                'validators' => [
                    [
                      'name' => 'NotEmpty',
                       'options' => [
                           'messages' => [\Zend\Validator\NotEmpty::IS_EMPTY => 'password is required'],
                        ],
                    ],
                ],
                'filters' => [
                    [
                       'name' => 'StringTrim',
                    ],
                ],
            ],
        ];
    }
}
