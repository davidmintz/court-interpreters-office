<?php
/**
 * module/Requests/src/Controller/WriteController.php
 */

namespace InterpretersOffice\Requests\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Authentication\AuthenticationServiceInterface;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Requests\Entity;
use InterpretersOffice\Entity\CourtClosing;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use InterpretersOffice\Admin\Service\Acl;
use InterpretersOffice\Service\DateCalculatorTrait;
use InterpretersOffice\Requests\Form;

use Zend\Mvc\MvcEvent;
use Zend\Http\Request;

/**
 *  update|create|cancel Requests
 *
 * this is for users in role "submitter"
 *
 */
class WriteController extends AbstractActionController implements ResourceInterface
{

    use DateCalculatorTrait;

    /**
     * objectManager instance.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
    * Request entity.
    *
    * @var Entity\Request
    */
    protected $entity;

    /**
     * authentication service
     *
     * @var AuthenticationServiceInterface;
     */
    protected $auth;

    /**
     * Acl - access control service
     *
     * @var Acl
     */
    protected $acl;

    /**
     * session
     *
     * @var \Zend\Session\Container
     */
    protected $session;

    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param AuthenticationServiceInterface $auth
     * @param Acl $acl
     */
    public function __construct(
        ObjectManager $objectManager,
        AuthenticationServiceInterface $auth,
        Acl $acl
    ) {
        $this->objectManager = $objectManager;
        $this->auth = $auth;
        $this->acl = $acl;
        $this->session = new \Zend\Session\Container("requests");
    }

    /**
     * implements ResourceInterface
     *
     * @return string
     */
    public function getResourceId()
    {
         return self::class;
    }


    /**
     * gets the Request entity we're working with
     *
     * @return Entity\Request
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * gets current user
     *
     * @return stdClass
     */
    public function getIdentity()
    {
        return $this->auth->getIdentity();
    }

    /**
     * onDispatch event Listener
     *
     * @param  MvcEvent $e
     * @return mixed
     */
    public function onDispatch($e)
    {

        $params = $this->params()->fromRoute();

        if (in_array($params['action'], ['update','cancel'])) {
            $entity = $this->objectManager->getRepository(Entity\Request::class)
                ->getRequestWithEvent($params['id']);

            if (! $entity) {
                return parent::onDispatch($e);
            }
            //return parent::onDispatch($e);
            $this->entity = $entity;
            /**
             * @todo
             * some optimization. this is bullshit.
             */
            $user = $this->objectManager->find(
                'InterpretersOffice\Entity\User',
                $this->auth->getIdentity()->id
            );
            $allowed = $this->acl->isAllowed(
                $user,
                $this,
                $params['action']
            );
            if (! $allowed) {
                $this->getResponse()->setStatusCode(403);
                $message = "Sorry, you are not authorized to {$params['action']}";
                if ($this->getRequest()->isXmlHttpRequest()) {
                    $data = [ 'error' => [
                                'code' => 403,
                                'message' => "$message this request.",
                            ]
                        ];
                    $response = $this->getResponse()
                        ->setContent(json_encode($data));
                    $response->getHeaders()
                            ->addHeaderline('Content-type: application/json');
                    return $response;
                }
                $viewRenderer = $e->getApplication()->getServiceManager()
                ->get('ViewRenderer');
                $url  = $this->getPluginManager()->get('url')
                ->fromRoute('requests/view', ['id' => $entity->getId()]);
                $content = $viewRenderer->render(
                    (new ViewModel())->setTemplate('denied')->setVariables([
                        'message' =>
                        "$message this <a href=\"$url\">request</a>."])
                );
                    $viewModel = $e->getViewModel()
                    ->setVariables(['content' => $content]);

                return $this->getResponse()
                    ->setContent($viewRenderer->render($viewModel));
            }
        }

        return parent::onDispatch($e);
    }



    /**
     * creates a new Request
     *
     * @return JsonModel
     */
    public function createAction()
    {
        $view = new ViewModel();
        $form = new Form\RequestForm(
            $this->objectManager,
            ['action' => 'create','auth' => $this->auth]
        );
        $view->form = $form;
        $entity = new Entity\Request();
        if (! $this->getRequest()->isPost()) {
            $repeat_id = $this->params()->fromRoute('id');
            if ($repeat_id) {
                $repo = $this->objectManager
                    ->getRepository(Entity\Request::class)
                    ->populate($entity, $repeat_id);
            }
        }
        $form->bind($entity);

        if ($this->getRequest()->isPost()) {
            try {
                $form->setData($this->getRequest()->getPost());
                if (! $form->isValid()) {
                    return new JsonModel(['validation_errors' =>
                        $form->getMessages()]);
                }
                // post-validation: make sure it is not a near-exact duplicate
                $repo = $this->objectManager->getRepository(Entity\Request::class);
                if ($repo->findDuplicate($entity)) {
                    return  new JsonModel(
                        ['validation_errors' => ['request' => ['duplicate' =>
                        [
                        'there is already a request with this date, time,
                        judge, type of event, defendant(s), docket, and language'
                        ]]]]
                    );
                }
                $form->postValidate();
                $this->objectManager->persist($entity);
                $this->objectManager->flush();
                $this->flashMessenger()->addSuccessMessage(
                    'Your request for interpreting services has been submitted. Thank you.'
                );
                return  new JsonModel(['status' => 'success','id' => $entity->getId()]);
            } catch (\Exception $e) {
                $this->getResponse()->setStatusCode(500);
                $this->events->trigger('error', $this, ['exception' => $e,
                    'details' => 'doing create in Requests module']);
                $this->getResponse()->setStatusCode(500);
                return new JsonModel(['error' => ['message' => $e->getMessage(),]]);
            }
        }

        return $view;
    }

    /**
     * updates a Request
     *
     * @return JsonModel
     */
    public function updateAction()
    {

        $entity = $this->entity;
        if (! $entity) {
            $this->flashMessenger()->addErrorMessage(
                "The request with id $id was not found in the database"
            );
            return $this->redirect()->toRoute('requests');
        }
        $form = new Form\RequestForm(
            $this->objectManager,
            ['action' => 'update','auth' => $this->auth]
        );
        $form->bind($entity);

        if (! $this->getRequest()->isPost()) {
            return  new ViewModel(['form' => $form, 'id' => $this->params()->fromRoute('id')]);
        }
        $this->getEventManager()->trigger('loadRequest',$this,
            ['entity'=>$entity,'entity_manager'=>$this->objectManager]);
        $data = $this->getRequest()->getPost()->get('request');
        $form->filterDateTimeFields(
            ['date','time'], $data,  'request'
        );
        $form->setData($this->getRequest()->getPost());
        if (! $form->isValid()) {
            return new JsonModel(['validation_errors' => $form->getMessages()]);
        }
        try {
            $form->postValidate();
            $event = $entity->getEvent();
            $log = $this->getEvent()->getApplication()->getServiceManager()->get('log');
            $log->info("controller NOW TRIGGERING updateRequest");
            $this->getEventManager()->trigger('updateRequest',$this,
                ['request'=>$entity,'entity_manager'=>$this->objectManager]);
            $this->objectManager->flush();
            $this->flashMessenger()->addSuccessMessage(
                'This request for interpreting services has been updated successfully. Thank you.'
            );
            return new JsonModel(['status' => 'success']);
        } catch (\Exception $e) {
            $this->getResponse()->setStatusCode(500);
            $this->events->trigger('error', $this, ['exception' => $e,
                'details' => 'doing update in Requests module']);
            return new JsonModel(['error' => ['message' => $e->getMessage(),]]);
        }
    }

    /**
     * cancels a Request
     *
     * @return JsonModel
     */
    public function cancelAction()
    {
        $id = $this->params()->fromRoute('id');
        $entity = $this->entity;
        if ($entity) {
            try {
                $entity->setCancelled(true);
                $this->getEventManager()->trigger('cancel',$this,
                    ['request'=>$entity,'entity_manager'=>$this->objectManager]);
                $this->objectManager->flush();
                $description = $this->params()->fromPost('description');
                $message = 'This request for interpreting services ';
                if ($description) {
                    $message .= "($description) ";
                }
                $message .= 'has now been cancelled. Thank you.';
                $this->flashMessenger()->addSuccessMessage($message);
                return new JsonModel([
                    'status' => 'success',
                    'id' => $id,
                ]);
            } catch (\Exception $e) {
                $this->getResponse()->setStatusCode(500);
                $this->events->trigger('error', $this, ['exception' => $e,
                    'details' => 'doing delete (cancel) in Requests module']);
                return new JsonModel(['error' => ['message' => $e->getMessage(),]]);
            }
        }
    }
}
