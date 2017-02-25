<?php
/**
 * module/InterpretersOffice/src/Controller/ExampleController.php.
 */

namespace InterpretersOffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Form\UserForm;
use InterpretersOffice\Entity;


/**
 *  AccountController.
 *
 *  For registration, password reset and email verification
 */

class AccountController extends AbstractActionController
{
    /**
     * objectManager instance.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function indexAction()
    {
        return new ViewModel();
    }

    public function registerAction()
    {
      return new ViewModel();   
    }

    public function verifyEmailAction()
    {
      return new ViewModel();   
    }

    public function requestPasswordAction()
    {
      
      return new ViewModel();
    }
    public function resetPasswordAction()
    {
       return new ViewModel(); 
    }
}
