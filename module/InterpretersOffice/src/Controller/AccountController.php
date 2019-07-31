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
use InterpretersOffice\Service\AccountManager;
use Zend\Session\Container as Session;
use Zend\InputFilter\InputFilterInterface;

use InterpretersOffice\Admin\Form\UserForm;
use InterpretersOffice\Controller\ExceptionHandlerTrait;
/**
 *  AccountController.
 *
 *  For registration, password reset and email verification.
 *
 */

class AccountController extends AbstractActionController
{
    use ExceptionHandlerTrait;
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
     * account manager service
     * @var AccountManager
     */
    private $accountManager;

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
     * Sets AccountManager instance.
     *
     * This is for our Controller factory to inject the AccountManager service.
     * (And we're doing it that way simply because I dislike long strings of
     * positional parameters in constructors. And passing an Options data
     * structure  of some kind -- e.g., an array -- seems like overkill.)
     *
     * @param AccountManager $accountManager
     * @return AccountController;
     */
    public function setAccountManager(AccountManager $accountManager)
    {
        $this->accountManager = $accountManager;

        return $this;
    }

    /**
     * index action
     * @return ViewModel
     */
    public function indexAction()
    {
        /*
        $thing = $this->layout();
        echo "INTERESTING FACTS:<br>";
        echo get_class($thing) . " is the class of \$this->layout() in our controller<br>";
        $plugins = $this->getEvent()->getApplication()->getServiceManager()->get('ControllerPluginManager');
        $thing2 = $plugins->get('layout');
        echo get_class($thing2) . " is the class of \$plugins->get('layout') in our controller<br>";
        */
        //return false;
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
            return new JsonModel(['valid' => false,
                'error' => 'malformed input data']);
        }

        // it's a 3-step form. the first two are handled as partial validation
        $form_step = $this->params()->fromQuery('step');
        $form = new RegistrationForm($this->objectManager, [
            'action' => 'create','auth_user_role' => 'anonymous',
            ]);
        $validation_group = [
            'csrf',
            'user' => [ 'person' => array_keys($params['user']['person'])]
        ];
        if ($form_step == 'fieldset-password') {
            array_push($validation_group['user'], 'password', 'password-confirm');
        }
        if ($form_step == 'fieldset-hat') {
            $form->preValidate($params['user']);
            $validation_group['user'][] = 'judges';
        }
        $form->setValidationGroup($validation_group);

        $form->setData($params);
        if (! $form->isValid()) {
            return new JsonModel([
                'validation_errors' => $form->getFlattenedErrorMessages(),
                ]);
        }

        return new JsonModel(['valid' => true, 'debug' => $validation_group]);
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
        $data = $input->get('user');
        $form->setData($input);
        $form->preValidate($data);
        if (! $form->isValid()) {
            return new JsonModel(
                ['validation_errors' => $form->getFlattenedErrorMessages()]
            );
        }
        try {
            $this->accountManager->register($user, $this->getRequest());
            $this->objectManager->flush();
            $this->getEventManager()->trigger(
                AccountManager::EVENT_REGISTRATION_SUBMITTED,
                $this,
                ['user' => $user]
            );
            return new JsonModel(
                ['validation_errors' => null, 'data' => $data,
                'status' => 'success']
            );
        } catch (\Exception $e) {
            return new JsonModel(
                [   'validation_errors' => null,
                    'data' => $data,
                    'status' => 'error',
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * email verification
     *
     * @return ViewModel
     */
    public function verifyEmailAction()
    {

        $id = $this->params()->fromRoute('id');
        $token = $this->params()->fromRoute('token');

        $result = $this->accountManager->verify(
            $id,
            $token,
            AccountManager::CONFIRM_EMAIL
        );
        if (! $result['error']) {
            $id = $result['data']['id'];
            $user = $this->objectManager->find(Entity\User::class, $id);
            $user->setActive(true);
            $this->objectManager->flush();
            $this->getEventManager()->trigger(
                AccountManager::EVENT_EMAIL_VERIFIED,
                $this->accountManager,
                ['user' => $user]
            );
            return new ViewModel(['user' => $user]);
        } else {
            return new ViewModel(['error' => $result['error']]);
        }
    }

    /**
     * handles password-reset requests
     *
     * @return ViewModel
     */
    public function requestPasswordAction()
    {
        if ($this->getRequest()->isPost()) {
            $inputFilter = $this->accountManager->getEmailInputFilter();
            $inputFilter->setData($this->params()->fromPost());
            if (! $inputFilter->isValid()) {
                return new JsonModel(['valid' => false,
                    'validation_errors' => $inputFilter->getMessages()]);
            }
            // valid POST
            $this->accountManager->requestPasswordReset(
                $inputFilter->getValue('email'),
                $this->getRequest()
            );
            return new JsonModel(
                ['valid' => true,'validation_errors' => null,]
            );
        }

        return new ViewModel();
    }

    /**
     * handles actual resetting of the user's password
     *
     * @return ViewModel
     */
    public function resetPasswordAction()
    {
        $session = new Session('password_reset');
        $hashed_id = $this->params()->fromRoute('id');

        if ($this->getRequest()->isGet()) {
            $token = $this->params()->fromRoute('token');
            $result = $this->accountManager->verify(
                $hashed_id,
                $token,
                AccountManager::RESET_PASSWORD
            );
            if ($result['data']) {
                $session->token = $token;
                $session->user_id = $result['data']['id'];
            }

            return new ViewModel(['result' => $result,'token' => $token]);
        }
        // else, it's a POST
        /** @var Zend\InputFilter\InputFilterInterface $filter */
        $filter = $this->accountManager->getPasswordInputFilter($session);
        $filter->setData($this->params()->fromPost());
        $valid = $filter->isValid();
        if ($valid) {
            $this->accountManager->purge($hashed_id);
            $result = $this->accountManager->resetPassword(
                $session,
                $filter->get('password')->getValue()
            );
            /** @todo if $result == false, deal with it -- even though it should
            * work if they get this far
            */
        }
        return new JsonModel([
            'validation_errors' => $filter->getMessages(),
            'valid' => $valid,
        ]);
    }
    /**
     * edit (user's own) account profile
     *
     * @return ViewModel
     */
    public function editAction()
    {
        if (! $this->auth->hasIdentity()) {
            $this->redirect()->toRoute('login');
            return;
        }
        $auth =  $this->auth->getIdentity();
        $em = $this->objectManager;
        /** @todo we WILL move this to a repo method */
        $dql = 'SELECT u, p, r, h, j
            FROM InterpretersOffice\Entity\User u
            JOIN u.person p JOIN u.role r JOIN p.hat h
            LEFT JOIN u.judges j
            WHERE u.id = :id';
        $user = $em->createQuery($dql)->setParameters(['id'=>$auth->id])
            ->getOneOrNullResult();
        /* ------------------------- */
        /** @var $person \InterpretersOffice\Entity\Person */
        $person = $user->getPerson();
        $form = new UserForm($em, [
            'action' => 'update',
            'auth_user_role' => $auth->role,
            'user' =>  $user,
            ]);
        $form->addCurrentPasswordElement()->addUniqueEmailValidator();
        /** @todo move this initialization somewhere else */
        /** @var InterpretersOffice\Admin\Form\UserFieldset $user_fieldset */
        $user_fieldset = $form->get('user');
        $user_fieldset->get('person')->setObject($person);
        $form->bind($user);
        $viewModel = (new ViewModel(['form'=>$form]));
        // they don't get to manipulate their own role, once set
        $form->getInputFilter()->get('user')->remove('role')->remove('id');
        if ($auth->role == 'submitter') {
            // we may decide we want to let a newly registered user correct her/his "hat" if there is
            // zero data history
            $related_entities = $this->objectManager->getRepository('InterpretersOffice\Entity\User')
                ->countRelatedEntities($user);
        } else {
            $related_entities = null;
        }
        $user_fieldset->addPasswordElements();
        $viewModel->related_entities = $related_entities;
        if ($this->getRequest()->isPost()) {
            return $this->postProfileUpdate($user,$form);
        } else {
            return $viewModel;
        }
    }

    /**
     * handles POST request to update user profile
     *
     * @param Entity\User $user
     * @param UserForm $form
     * @return JsonModel
     */
    public function postProfileUpdate(Entity\User $user, UserForm $form)
    {
        $data = $this->getRequest()->getPost();
        $person = $user->getPerson();
        $person_before = ['mobile'=>$person->getMobilePhone(),'office'=>$person->getOfficePhone() ];
        $user_params = $data->get('user');
        $user_params['person']['hat'] = $person->getHat()->getId();
        $user_params['person']['id'] = $person->getId();
        if ($user_params['password'] or $user_params['password-confirm']) {
            $form->addPasswordValidators();
        }
        $data->set('user',$user_params);
        $form->setData($data);
        if (!$form->isValid()) {
            return new JsonModel(['validation_errors'=>$form->getMessages()]);
        }
        try {
            $this->objectManager->flush();
            $before = $this->auth->getIdentity();
            $after = (object)[
                'lastname' => $person->getLastname(),
                'firstname' => $person->getFirstname(),
                'email' => $person->getEmail(),
                'hat' => (string)$person->getHat(),
                'username' => $user->getUserName(),
                'id' => $user->getId(),
                'person_id' => $person->getId(),
                'role' => (string)$user->getRole(),
                'judge_ids' => isset($user_params['judges']) ? $user_params['judges']:[]
            ];
            $this->auth->getStorage()->write($after);
            $this->getEventManager()->trigger(
                AccountManager::USER_ACCOUNT_MODIFIED,
                $this,
                compact('user','before','after','person_before')
            );
        } catch (\Throwable $e) {
            return $this->catch($e);
        }

        return new JsonModel(['status'=>'success',]);
    }
}
