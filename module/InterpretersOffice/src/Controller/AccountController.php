<?php
/**
 * module/InterpretersOffice/src/Controller/AccountController.php.
 */

namespace InterpretersOffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Entity;
use InterpretersOffice\Form\User\RegistrationForm;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Form\FormInterface;

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
     * authentication service
     *
     * @var AuthenticationServiceInterface
     */
    protected $auth;
    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param AuthenticationServiceInterface
     */
    public function __construct(ObjectManager $objectManager, AuthenticationServiceInterface $auth)
    {
        $this->objectManager = $objectManager;
        $this->auth = $auth;
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
     * partial validation
     *
     * @return JsonModel
     */
    public function validateAction()
    {
        $params = $this->params()->fromPost();

        if (! isset($params['user']) or ! isset($params['user']['person'])) {
            return new JsonModel(['valid'=>false,
                'error'=>'malformed input data']);
        }
        // it's a 3-step form. the first two are handled as partial validation
        $form_step = $this->params()->fromQuery('step');
        $form = new RegistrationForm($this->objectManager, [
            'action' => 'create','auth_user_role' => 'anonymous',
            ]);
        $validation_group = ['user'=>
            [ 'person'=> array_keys($params['user']['person'])]
        ];
        if ($form_step == 'fieldset-hat') {
            $shit = $form->preValidate($params['user']);
            $validation_group['user'][] = 'judges';
        }
        $form->setValidationGroup($validation_group);

        $form->setData($params);
        if (! $form->isValid()) {
            $messages = $form->getMessages()['user'];
            return new JsonModel(['validation_errors'=> $messages,'debug'=>$shit]);
        }
        return new JsonModel(['valid'=>true, 'debug'=>$shit]);
    }

    /**
     * partial validation
     *
     * @return JsonModel
     */
    public function __validateAction()
    {
        $params = $this->params()->fromPost();
        $step = $this->params()->fromQuery('step');
        $form = new RegistrationForm($this->objectManager, [
            'action' => 'create','auth_user_role' => 'anonymous',
            ]);
        if ($step == 'fieldset-personal-data') {
            // step one: 'person' fields only
            $group = ['user'=>['person'=>array_keys($params['user']['person'])]];
        } else {
            // step two: 'person' and possibly 'user'(judges).
            // attention: after much fiddling, we find that this
            // is the notation that works!
            $group = [
                'user'=> [
                    'judges',
                    'person' => array_keys($params['user']['person']),
                ]
            ];
        }
        $form->setValidationGroup($group);

        $form->setData($params);
        if (! $form->isValid()) {
            $messages = $form->getMessages()['user'];
            return new JsonModel(['validation_errors'=> $messages]);
        }
        return new JsonModel(['valid'=>true, 'debug'=>$group]);
    }

    /**
     * registers a new user account
     *
     * @return ViewModel
     */
    public function registerAction()
    {

        $form = new RegistrationForm($this->objectManager, [
            'action' => 'create','auth_user_role' => 'anonymous',
            ]);
        if (! $this->getRequest()->isPost()) {
            return new ViewModel(['form' => $form]);
        }
        // handle POST
        $user = new Entity\User();
        $request = $this->getRequest();
        $form->bind($user);
        $input = $request->getPost();
        $user = $input->get('user');

        $form->setData($input);
        if (! $form->isValid()) {
            printf('<pre>%s</pre>',print_r($form->getMessages(),true));
        }
        //printf('<pre>%s</pre>',print_r($form->getData(\Zend\Form\FormInterface::VALUES_AS_ARRAY),true));

        return new ViewModel(['form' => $form]);
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
    /**
     * edit account profile
     *
     * @return ViewModel
     */
    public function editAction()
    {
        if (! $this->auth->hasIdentity()) {
            $this->redirect()->toRoute('auth');
            return;
        }
        return new ViewModel();
    }
}
