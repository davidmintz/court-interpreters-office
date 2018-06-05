<?php
/**
 * module/Admin/src/Form/UserFieldset.php.
 */

namespace InterpretersOffice\Admin\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use InterpretersOffice\Form\ObjectManagerAwareTrait;

use InterpretersOffice\Form\PersonFieldset;

use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\Input;
use Zend\Validator\Identical;
//use Zend\Authentication\AuthenticationServiceInterface;

//use InterpretersOffice\Admin\Service\Authentication\AuthenticationAwareInterface;


/**
 *
 * Fieldset for User entity
 */
class UserFieldset extends Fieldset implements InputFilterProviderInterface, ObjectManagerAwareInterface
{

    use ObjectManagerAwareTrait;

    /**
     * name of the fieldset.
     * @var string
     */
    protected $fieldset_name = 'user';

    /**
     * current controller action
     *
     * @var string $action
     */
    protected $action;

    /**
     * Authentication service
     * @todo consider whether this is really necessary
     *
     * @var AuthenticationServiceInterface $auth
     */
    protected $auth;

    /**
     * role of currently authenticationed user
     *
     * @var string $auth_user_role
     */
    protected $auth_user_role;

    /**
     * user, if we're an update
     *
     * @var \InterpretersOffice\Entity\User
     */
    protected $user;

    /**
     * constructor
     *
     * @param ObjectManager $objectManager
     * @param array $options
     * @throws \RuntimeException
     */
    public function __construct(ObjectManager $objectManager, array $options)
    {
        if (! isset($options['action'])) {
            throw new \RuntimeException('missing "action" option in UserFieldset constructor');
        }
        if (! in_array($options['action'], ['create', 'update'])) {
            throw new \RuntimeException('invalid "action" option in UserFieldset constructor');
        }
        $this->action = $options['action'];
        //printf('DEBUG action is %s in UserFieldset line %d<br>',$this->action,__LINE__);
        unset($options['action']);

        if (! isset($options['auth_user_role'])) {
            throw new \RuntimeException('missing "role" option in UserFieldset constructor');
        }
        $this->auth_user_role = $options['auth_user_role'];
        unset($options['auth_user_role']);
        if (! empty($options['user'])) {
            $this->user = $options['user'];
            unset($options['user']);
        }
        parent::__construct($this->fieldset_name, $options);
        $this->objectManager = $objectManager;
        $this->setHydrator(new DoctrineHydrator($objectManager, true))
                ->setUseAsBaseFieldset(true);

        $this->addElements();
    }
    /**
     * adds elements to this fieldset
     *
     * @return UserFieldset
     */
    protected function addElements()
    {

        $this->add([
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id',
            'required' => true,
            'allow_empty' => true,
        ]);
        $this->add([
            'type' => 'Zend\Form\Element\Text',
            'name' => 'username',
            'options' => [
                'label' => 'username',
            ],
             'attributes' => [
                'class' => 'form-control',
                'id' => 'username',
             ],
        ]);
        if ($this->user) {
            $hat = $this->user->getPerson()->getHat();
        } else {
            $hat = null;
        }
        $this->add(
            [
            'name' => 'role',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'options' => [
                'object_manager' => $this->objectManager,
                'target_class' => 'InterpretersOffice\Entity\Role',
                'label' => 'role',
                'find_method' => [
                    'name' => 'getRoles',
                    'params' => [
                        'auth_user_role' => $this->auth_user_role,
                        'hat' => $hat,
                    ],
                 ],
                'property' => 'name',
            ],
            'attributes' => [
                'class' => 'form-control',
                'id' => 'role',
            ],
            ]
        );
        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'active',
            'required' => true,
            'allow_empty' => false,
            'options' => [
                'label' => 'active',
                'use_hidden_element' => true,
                'checked_value' => 1,
                'unchecked_value' => 0,
            ],
            'attributes' => [
                'value' => 1,
                'id' => 'user-active',
            ],
        ]);
         // hack designed to please HTML5 validator
        $element = $this->get('role');
        $options = $element->getValueOptions();
        array_unshift($options, [
           'label' => ' ',
           'value' => '',
           'attributes' => [
               'label' => ' ',
           ],
        ]);
        $element->setValueOptions($options);
        $fieldset = new PersonFieldset(
            $this->objectManager,
            [
                'action' => $this->action,
                'use_as_base_fieldset' => false,
                'auth_user_role' => $this->auth_user_role,
            ]
        );

        $this->add($fieldset);

        return $this;
    }
    /**
     * adds password and confirm-password elements
     *
     * @return UserFieldset
     */
    public function addPasswordElements()
    {

        $this->add([
            'type' => 'password','name'=>'password',
            'attributes' => ['class'=>'form-control','id'=>'password']
        ]);
        $this->add([
            'type' => 'password','name'=>'password-confirm',
            'attributes' => ['class'=>'form-control','id'=>'password-confirm']
        ]);

        return $this;
    }

    /**
     * adds password validation
     *
     * @param InputFilterInterface $inputFilter
     */
    public function addPasswordValidators(InputFilterInterface $inputFilter)
    {
        //return;
        $input = new Input('password');
        $chain = $input->getValidatorChain();
        $input->getFilterChain()->attachByName('StringTrim');
        $chain->attachByName('NotEmpty', [
                'required' => true,
                'messages' => ['isEmpty' => 'password field is required',]
                , true])
            ->attachByName('StringLength', ['min'=>8,'max'=>'150','messages'=>[
                'stringLengthTooLong' => 'password length exceeds maximum (150 characters)',
                'stringLengthTooShort' => 'password length must be a minimum of 8 characters',
        ]],true);
        $inputFilter->add($input);
        $confirmation_input = new Input('confirm-password');
        $confirmation_input->getFilterChain()->attachByName('StringTrim');
        $chain = $confirmation_input->getValidatorChain();
        //$shit = new \Zend\Validator\ZendValidatorIdentical();
        //\Zend\Validator\Identical::NOT_SAME
        $chain->attachByName('Identical',['token'=>'password','messages'=> [
            'notSame' => 'password confirmation field does not match'
        ]]);
        $inputFilter->add($confirmation_input);

    }

    /**
     * implements InputFilterProviderInterface
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            'id' => [
                'required' => true,
                'allow_empty' => true,
            ],
            'username' => [
                'required' => true,
                'allow_empty' => false,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => [
                                'isEmpty' => 'username is required (you can '
                                .'use the email address)',
                            ],
                        ],
                    ],
                ],
                /** @todo stringlength validation */
            ],
            'role' => [
                'required' => true,
                'allow_empty' => false,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages' => [
                                'isEmpty' => 'role is required',
                            ],
                        ],
                    ],
                ],
            ],
            'active' => [
                'required' => false,
                'allow_empty' => true,
                'filters' => [
                    [
                        'name' => 'Zend\Filter\Boolean'
                    ],
                ],
                'validators' => [
                    [
                        'name' => 'Zend\Validator\Callback',
                        'options' => [
                            'callback' => function ($value, $context) {
                                $person_active = $context['person']['active'];
                                $user_active = $value;
                                if ($user_active && ! $person_active) {
                                    return false;
                                }
                                if (! $person_active && $user_active) {
                                    return false;
                                }
                                return true;
                            },
                            'messages' => [
                                \Zend\Validator\Callback::INVALID_VALUE
                                => 'user account-enabled and person "active" settings are inconsistent',
                            ],
                        ],
                    ],
                ],
            ]
        ];
    }
}
