<?php
/**
 * module/Requests/src/Controller/IndexController.php
 */

namespace InterpretersOffice\Requests\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Authentication\AuthenticationServiceInterface;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Requests\Entity;

use InterpretersOffice\Requests\Form;

/**
 *  IndexController for Requests module
 *
 */
class IndexController extends AbstractActionController
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
     * @var AuthenticationServiceInterface;
     */
    protected $auth;

    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param AuthenticationServiceInterface $auth
     */
    public function __construct(ObjectManager $objectManager, AuthenticationServiceInterface $auth)
    {
        $this->objectManager = $objectManager;
        $this->auth = $auth;
    }


    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        return new ViewModel();
    }

    public function testAction()
    {
        $view = new ViewModel();
        $view->setTemplate('interpreters-office/requests/index/index.phtml');

        return $view;
    }

    /**
     * displays the user's requests
     *
     * @return ViewModel
     */
    public function listAction()
    {
        $repo = $this->objectManager->getRepository(Entity\Request::class);
        $paginator = $repo->list(
            $this->auth->getIdentity(),
            $this->params()->fromQuery('page',1)
        );
        if ($paginator) {
            $ids = array_column($paginator->getCurrentItems()->getArrayCopy(),'id');
            $defendants = $repo->getDefendants($ids);
        } else {
            $defendants = [];
        }
        return new ViewModel(['paginator' => $paginator,'defendants'=>$defendants ]);
    }

    /**
     * creates a new Request
     *
     * @return JsonModel
     */
    public function createAction()
    {
        $view = new ViewModel();
        $view->setTemplate('interpreters-office/requests/index/form.phtml');

        $form = new Form\RequestForm($this->objectManager,
            ['action'=>'create','auth'=>$this->auth]);
        $view->form = $form;
        // debug, test...
        // $repo = $this->objectManager->getRepository(\InterpretersOffice\Entity\Location::class);
        // $view->debug_data = $repo->getLocationOptionsForHat("USPO");
        $entity = new Entity\Request();
        $form->bind($entity);
        if ($this->getRequest()->isPost()) {
            try {
                // see if this works.
                //$person_id = $this->auth->getIdentity()->person_id;
                // $form->get('request')
                //     ->add(['type'=>'hidden','name'=> 'submitter'])
                //     ->setValue($person_id);
                $form->setData($this->getRequest()->getPost());
                if (! $form->isValid()) {
                    return new JsonModel(['validation_errors' =>
                        $form->getMessages()]);
                }
                $this->objectManager->persist($entity);
                $this->objectManager->flush();
                return  new JsonModel(['status'=> "valid. to be continued..."]);

            } catch (\Exception $e) {
                $this->getResponse()->setStatusCode(500);
                return new JsonModel(['message'=>$e->getMessage()]);
            }
        }
        // $repo = $this->objectManager->getRepository(\InterpretersOffice\Entity\Judge::class);
        // $options = $repo->getJudgeOptionsForUser($this->auth->getIdentity());
        // print_r($options);
        return $view;
    }

    /**
     * updates a Request
     *
     * @return JsonModel
     */
    public function updateAction()
    {
        $view = new ViewModel();
        $view->setTemplate('interpreters-office/requests/index/form.phtml');
        $id = $this->params()->fromRoute('id');
        $form = new Form\RequestForm($this->objectManager,
            ['action'=>'update','auth'=>$this->auth]);

        $view->setVariables(['form' => $form, 'id' => $id]);

        return $view;
    }
}
