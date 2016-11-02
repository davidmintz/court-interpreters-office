<?php
/**
 * module/Application/src/Controller/LanguagesController.php
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Interop\Container\ContainerInterface;

use Application\Form\Factory\AnnotatedFormFactory;
use Application\Entity\Language;

/**
 *  IndexController
 * 
 *  Currently, just for making sure the application runs, basic routing is 
 *  happening, service container is working, views are rendered, etc.
 */

class LanguagesController extends AbstractActionController
{
    /** 
     * service manager.
     * 
     * @var ContainerInterface 
     */
    protected $serviceManager;
    
    /**
     * constructor
     * 
     * @see Application\Controller\Factory\SimpleEntityControllerFactory
     * @param ContainerInterface $serviceManager
     */
    public function __construct(ContainerInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        /** @todo make this an invokable we can get out of the container? */
        $this->factory = new AnnotatedFormFactory();
        
    }
    /**
     * index action
     * 
     * @return ViewModel
     */
    public function indexAction()
    {
        
    }
    public function createAction() {
        
        $language = new Language();
        $factory = $this->factory;
        $form = $factory($this->serviceManager,'language',['object'=>$language]);
        
        $viewModel = new ViewModel(['form'=>$form,'title'=>'add a language']);
        $viewModel->setTemplate('application/languages/form.phtml');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->bind($language);
            $form->setData($data);
            if (! $form->isValid()) {
                echo "shit NOT valid";
                \Zend\Debug\Debug::dump($form->getMessages());
                return $viewModel;
            } else {
                echo "SHIT IS VALID ?!?";
                try {
                    $em = $this->serviceManager->get('entity-manager');
                    $em->persist($language);
                    $em->flush();
                    $this->flashMessenger()
                            ->addSuccessMessage("This language has been added.");
                    $this->redirect()->toRoute('languages');
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
                return $viewModel;
            }
            
        }
        return $viewModel;
    }
}
