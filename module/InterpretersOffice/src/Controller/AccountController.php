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
 *  For registration, password reset and email verification.
 *  Very much incomplete.
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

    /**
     * index action
     * @return ViewModel
     */
    public function indexAction()
    {
        return new ViewModel();
    }
    /**
     * registers a new user account
     *
     * @return ViewModel
     */
    public function registerAction()
    {
        return new ViewModel();
    }

    /**
     * email verification
     * s
     * @return ViewModel
     */
    public function verifyEmailAction()
    {
        return new ViewModel();
    }

    /**
     * handles password-reset requests
     *
     * @return ViewModel
     */
    public function requestPasswordAction()
    {

        return new ViewModel();
    }

    /**
     * handles actual resetting of the user's password
     *
     * @return ViewModel
     */
    public function resetPasswordAction()
    {
        return new ViewModel();
    }
}
