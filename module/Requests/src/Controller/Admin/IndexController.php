<?php /** module/Requests/src/Controller/Admin/IndexController.php */

namespace InterpretersOffice\Requests\Controller\Admin;

use Doctrine\Common\Persistence\ObjectManager;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Stdlib\ArrayObject;
use InterpretersOffice\Requests\Form\ConfigForm;
use InterpretersOffice\Requests\Entity\Request;
use Laminas\Session\Container as Session;

/**
 * admin controller for Requests module
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
     * relative path to configuration dir
     *
     * @var string
     */
    protected $config_dir = 'module/Requests/config';

    private $session;

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
        $this->session = new Session('admin_requests');
    }

    /**
     * index action
     *
     */
    public function indexAction()
    {

        $repo = $this->objectManager->getRepository(Request::class);
        $xhr = $this->getRequest()->isXmlHttpRequest();
        if (! $xhr) {
            if ($this->session->tabs) { // use session
                $active = $this->session->tabs['active'];
                $page = $this->session->tabs['page'];
                if ($active == 'scheduled-requests') {
                    $method = 'getScheduledRequests';
                } else {
                    $method = 'getPendingRequests';
                }
            } else { // use defaults
                $active = 'pending-requests';
                $method = 'getPendingRequests';
                $page   = 1;
                $this->session->tabs = [
                    'active' => $active,
                    'page' => $page,
                ];
            }
            $paginator = $repo->$method($page);
        } else {
            // YES xhr, and they asked for index, therefore they are asking for pending requests
            $active = 'pending-requests';
            $page   = $this->params()->fromQuery('page',1);
            $this->session->tabs = [
                'active' => $active,
                'page' => $page,
            ];
            $paginator = $repo->getPendingRequests($page);
        }
        if ($paginator) {
            $defendants = $repo->getDefendantNamesForCurrentPage($paginator);
        } else {
            $defendants = [];
        }
        $data = compact('paginator', 'defendants','page','active');
        $data['csrf'] = (new \Laminas\Validator\Csrf('csrf'))->getHash();
        if ($xhr) {
            return (new ViewModel($data))
                ->setTerminal(true)
                ->setTemplate('partials/pending-requests-table');
        }

        return $data;
    }
    /**
     * controller action for configuring Request listeners
     *
     * @return mixed
     */
    public function configAction()
    {
        $form = new ConfigForm();
        $data = $form->data;
        $object = new \Laminas\Stdlib\ArrayObject($data);
        $form->bind($object);
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            return new JsonModel($data);
        }
        $acl = $this->getEvent()->getApplication()->getServiceManager()
            ->get('acl');
        $role = $this->auth->getIdentity()->role;
        $allowed = $acl->isAllowed($role, self::class, 'updateConfig');
        //echo "FUCK? $role allowed? ".($allowed ? "true":"false");
        return new ViewModel(['form' => $form,'update_allowed' => $allowed,
            'customized_settings' =>
                file_exists($this->config_dir.'/custom.event-listeners.json')
        ]);
    }
    /**
     * updates the Request event-listener configuration
     *
     * @return JsonModel
     */
    public function updateConfigAction()
    {
        $data = $this->getRequest()->getPost()->toArray();
        if (isset($data['restore-defaults']) && $data['restore-defaults']) {
                return $this->restoreDefaults();
        }
        $defaults = json_decode(file_get_contents(
            "{$this->config_dir}/default.event-listeners.json"
        ), true);
        $custom_settings_path = "{$this->config_dir}/custom.event-listeners.json";
        $customized = file_exists($custom_settings_path);
        $response = ['custom_settings_were_found' => $customized];

        if ($defaults == $data) {
            // remove the custom settings if they exist
            if ($customized) {
                unlink($custom_settings_path);
                $response['deleted_custom_settings'] = true;
            } else {
                $response['deleted_custom_settings'] = false;
            }
        } else {
            file_put_contents($custom_settings_path, json_encode($data));
            $what = $customized ? 'updated' : 'created';
            $response["{$what}_custom_settings"] = true;
        }

        return new JsonModel($response);
    }

    /**
     * restores default request-manager event-listener configuration
     *
     * @return JsonModel
     */
    public function restoreDefaults()
    {
        unlink("{$this->config_dir}/custom.event-listeners.json");

        return new JsonModel(['deleted_custom_settings' => true]);
    }

    /**
     * view request details
     *
     * @return mixed
     */
    public function viewAction()
    {
        $id = $this->params()->fromRoute('id');
        $entity = $this->objectManager->getRepository(Request::class)
            ->getRequest($id);
        $validator = new \Laminas\Validator\Csrf('csrf');
        $token = $validator->getHash();
        return ['request' => $entity,'csrf' => $token];
    }

    public function getScheduledRequestsAction()
    {
        $repo = $this->objectManager->getRepository(Request::class);
        $page = $this->params()->fromQuery('page',1);
        $active = 'scheduled-requests';
        /** @var \Laminas\Paginator\Paginator $paginator */
        $paginator = $repo->getScheduledRequests($page);
        $defendants = $repo->getDefendantNamesForCurrentPage($paginator);

        $data = compact('paginator','defendants','page','active');
        $view = new ViewModel($data);
        if ($this->getRequest()->isXmlHttpRequest()) {
            $view
                ->setTemplate('partials/scheduled-requests-table')
                ->setTerminal(true);
        }
        $this->session->tabs = [
            'active' => 'scheduled-requests',
            'page' => $page,
        ];

        return $view;
    }

    /**
     * adds a request to the schedule
     *
     * internally, this means create an Event entity with data from a Request
     * entity.
     * @return JsonModel
     */
    public function scheduleAction()
    {
        $request_id = $this->params()->fromRoute('id');
        $validator = new \Laminas\Validator\Csrf('csrf');
        $token = $this->params()->fromPost('csrf');
        if (! $validator->isValid($token)) {
            return new JsonModel(['status' => 'error','message' =>
                'Invalid or missing security token. '
                .'You may need to refresh this page and try again.']);
        }
        $repository = $this->objectManager->getRepository(Request::class);
        $result = $repository->createEventFromRequest($request_id);

        return new JsonModel($result);
    }
}
