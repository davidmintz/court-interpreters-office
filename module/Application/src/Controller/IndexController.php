<?php
/**
 * module/Application/src/Controller/IndexController.php
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Interop\Container\ContainerInterface;

/**
 *  IndexController
 * 
 *  Currently, just for making sure the application runs, basic routing is 
 *  happening, service container is working, views are rendered, etc.
 */

class IndexController extends AbstractActionController
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
     * @see Application\Controller\Factory\IndexControllerFactory
     * @param ContainerInterface $serviceManager
     */
    public function __construct(ContainerInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    /**
     * index action
     * 
     * @return ViewModel
     */
    public function indexAction()
    {
        
    }
    /**
     * temporary action for experimenting and doodling.
     * 
     * this demonstrates that we can build a form from annotations
     * and bind the form to a Doctrine entity. our thinking is to 
     * use this technique for creating and processing forms for create and
     * update actions on the simple or relatively simple entities.  
     * 
     */
    public function testAction()
    {
        $builder = new  \Zend\Form\Annotation\AnnotationBuilder($this->serviceManager->get('entity-manager'));
        $form    = $builder->createForm(\Application\Entity\Language::class);
        $em      = $this->serviceManager->get('entity-manager');
        $form->setHydrator(new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($em));
        $viewModel = new ViewModel(['form' => $form]);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $language = new \Application\Entity\Language();
            $form->bind($language);
            $form->setData($data);
            if (! $form->isValid()) {
                return $viewModel;
            }
            $em->persist($language);
            $em->flush();
            $this->flashMessenger()->addMessage("congratulations! you inserted the language $language.");
            return $this->redirect()->toRoute('home');
        }
        return new ViewModel(['form'=>$form]);
    }
}
