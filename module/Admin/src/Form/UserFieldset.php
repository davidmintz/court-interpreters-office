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
     * constructor
     * 
     * @param ObjectManager $objectManager
     * @param array $options
     * @throws \RuntimeException
     */
	public function __construct(ObjectManager $objectManager, Array $options)
	{
        if (!isset($options['action'])) {
            throw new \RuntimeException('missing "action" option in UserFieldset constructor');
        }
        if (!in_array($options['action'], ['create', 'update'])) {
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
        // maybe we can get by with just the "role," which is in the session
        /*
        if (! $options['auth'] instanceof AuthenticationServiceInterface) {
            throw new \RuntimeException(sprintf(
                   'UserFieldset constructor expected instance of %s, got %s',
                   AuthenticationServiceInterface::class,
                    is_object($options['auth']) ? get_class($options['auth'])
                     : gettype($options['auth'])
            ));
        }
        */
        parent::__construct($this->fieldset_name, $options);
        $this->objectManager = $objectManager;
        $this->setHydrator(new DoctrineHydrator($objectManager, true))
                //->setObject(new Entity\Person())
                ->setUseAsBaseFieldset(true);        
        //foreach ($this->elements as $element) { $this->add($element);  }
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
                    'params' => ['auth_user_role' => $this->auth_user_role ],
                 ],
                'property' => 'name',
            ],
            'attributes' => [
                'class' => 'form-control',
                'id' => 'role',
            ],
        ]
        );
        $this->add( [
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
        $fieldset = new PersonFieldset($this->objectManager,
            [
                'action' => $this->action,
                'use_as_base_fieldset' => false,
                'auth_user_role' => $this->auth_user_role,
            ]);

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
        // to be implemented
        return $this;
    }

	/**
     * implements InputFilterProviderInterface
     * 
     * @return array
     */
    public function getInputFilterSpecification() {
        return [
            'id' => [
                'required' => true,
                'allow_empty' => true,
            ],
            'username' => [
                'required' => true,
                'allow_empty' => true,
                /** @todo stringlength validation */
            ],
            'role' => [
                'required' => false,
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
                        'name'=>'Zend\Filter\Boolean'
                    ],
                ],
                'validators' => [
                    [
                        'name' => 'Zend\Validator\Callback',
                        'options' => [
                            'callback' => function($value,$context) {
                                $person_active = $context['person']['active'];
                                $user_active = $value;
                                if ($user_active && ! $person_active) {
                                    return false;
                                }
                                if (! $person_active && $user_active ) {
                                    return false;
                                }
                                return true;
                            },
                            'messages' => [
                                \Zend\Validator\Callback::INVALID_VALUE => 'user account-enabled and person "active" settings are inconsistent',
                            ],                            
                        ],                        
                    ],
                ],
            ]
        ];
    }
}
