<?php /** module/Requests/src/Controller/Admin/IndexController.php */

namespace InterpretersOffice\Requests\Controller\Admin;

use Doctrine\Common\Persistence\ObjectManager;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\AuthenticationServiceInterface;
//use Zend\Stdlib\Glob;
use Zend\Stdlib\ArrayObject;
use InterpretersOffice\Requests\Form\ConfigForm;
use InterpretersOffice\Requests\Entity\Request;


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
     * constructor.
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager, AuthenticationServiceInterface $auth)
    {
        $this->objectManager = $objectManager;
        $this->auth = $auth;

    }

    public function indexAction()
    {

        $repo = $this->objectManager->getRepository(Request::class);
        $pending = $repo->getPendingRequests();

        if ($pending) {
            $data = $pending->getCurrentItems()->getArrayCopy();
            // remind me to refactor this
            $ids = array_column(array_column($data,0),'id');
            $defendants = $repo->getDefendants($ids);
        } else {
            $defendants = [];
        }

        return compact('pending','defendants');
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
        $object = new \Zend\Stdlib\ArrayObject($data);
        $form->bind($object);
        $form->setObject($object);
        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost();
            return new JsonModel($data);
        }
        $acl = $this->getEvent()->getApplication()->getServiceManager()
            ->get('acl');
        $role = $this->auth->getIdentity()->role;
        $allowed = $acl->isAllowed($role,self::class,'updateConfig');
        return new ViewModel(['form'=>$form,'update_allowed'=>$allowed]);

    }
    /**
     * updates the Request event-listener configuration
     *
     * @return JsonModel
     */
    public function updateConfigAction()
    {
        $data = $this->getRequest()->getPost()->toArray();

        $defaults = json_decode(file_get_contents(
            'module/Requests/config/default.event-listeners.json'),true);
        $custom_settings_path = 'module/Requests/config/custom.event-listeners.json';
        $customized = file_exists($custom_settings_path);
        $response = ['customized_settings_were_found' => $customized];

        if ($defaults == $data) {
            // remove the custom settings if they exist
            if ($customized){
                unlink($custom_settings_path);
                $response['deleted_custom_settings'] = true;
            } else {
                $response['deleted_custom_settings'] = false;
            }
        } else {
            file_put_contents($custom_settings_path, json_encode($data));
            $what = $customized ? 'updated':'created';
            $response["{$what}_custom_settings"] = true;
        }

        return new JsonModel($response);
    }
    /**
     * view request details
     *
     * @return mixed
     */
    public function viewAction()
    {
        $id = $this->params()->fromRoute('id');
        $entity =  $this->objectManager->getRepository(Request::class)->view($id);
        //echo gettype($entity);
        return ['request'=>$entity];
    }

}
