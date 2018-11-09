<?php /** module/Requests/src/Controller/Admin/IndexController.php */

namespace InterpretersOffice\Requests\Controller\Admin;

use Doctrine\Common\Persistence\ObjectManager;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Stdlib\Glob;
use Zend\Stdlib\ArrayObject;
use InterpretersOffice\Requests\Form\ConfigForm;

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

    public function configAction()
    {
        $form = new ConfigForm();
        $form->setUseAsBaseFieldset(true);
        $data = $form->default_values;
        $object = new \Zend\Stdlib\ArrayObject($data);
        $form->bind($object);
        $form->setObject($object);
        //$form->setData($object);
        //$form->setIn
        // echo 'valid: '.($form->isValid()?'yes':'no');
        // echo "<br>foo? ",$data['foo'];
        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost();
            //$string = json_encode($data);
            //file_put_contents('data/settings.json',$string);
            return new JsonModel($data);
        }

        return new ViewModel(['form'=>$form]);

    }
}
