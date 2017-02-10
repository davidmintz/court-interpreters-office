<?php
/**
 * module/Admin/src/Form/UserFieldset.php.
 */

namespace InterpretersOffice\Admin\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

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
     * constructor
     * 
     * options: 
     * 	
     * 
     */
	public function __construct(ObjectManager $objectManager, $options = [])
	{


	}

	//public function setAuthenticationService(AuthenticationService $auth){}


}