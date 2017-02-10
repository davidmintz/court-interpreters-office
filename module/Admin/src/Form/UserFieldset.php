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
     * @var string $role
     */
    protected $role;
    
    /**
     * constructor
     * 
     * options: 
     * 	
     */
	public function __construct(ObjectManager $objectManager, $options = [])
	{
        if (!isset($options['action'])) {
            throw new \RuntimeException('missing "action" option in UserFieldset constructor');
        }
        if (!in_array($options['action'], ['create', 'update'])) {
            throw new \RuntimeException('invalid "action" option in UserFieldset constructor');
        }
        $this->action = $options['action'];
        unset($options['action']);
        
        if (! isset($options['role'])) {
            throw new \RuntimeException('missing "role" option in UserFieldset constructor');
        }
        $this->role = $options['role']; unset($options['role']);
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
    }

	//public function setAuthenticationService(AuthenticationService $auth){}
    public function getInputFilterSpecification() {
        return [
            'id' => [
                'required' => true,
                'allow_empty' => true,
            ],
            'username' => [
                'required' => true,
                'allow_empty' => false,
            ], 
        ];
    }

}