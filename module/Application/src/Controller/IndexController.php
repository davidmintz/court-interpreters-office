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
     * and bind the form to a Doctrine entity, then add more elements
     */
    public function testAction()
    {
        // http://stackoverflow.com/questions/12002722/using-annotation-builder-in-extended-zend-form-class/18427685#18427685
        $em      = $this->serviceManager->get('entity-manager');
        $builder = new  \Zend\Form\Annotation\AnnotationBuilder($this->serviceManager->get('entity-manager'));
        // could not get validators to run when person stuff was added as a 
        // fieldset.
        /*
        $fieldset   = $builder->createForm(\Application\Entity\Person::class);
        $form = new \Zend\Form\Form('whatever');
        $form->setHydrator(new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($em));
        $form->add($fieldset);
        $fieldset->setUseAsBaseFieldset(true);
        */
        $form =  $builder->createForm(\Application\Entity\Person::class);
        $form->setHydrator(new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($em));
        
        $element = new \DoctrineModule\Form\Element\ObjectSelect('hat',
        [
                    'object_manager' => $em,
                    'target_class' => 'Application\Entity\Hat',
                    'property' => 'name',
                    'label' => 'hat',
                    'display_empty_item' => true,        
        ]);
        $filter = $form->getInputFilter();
        \Zend\Debug\Debug::dump(get_class_methods($filter));
        //https://docs.zendframework.com/zend-inputfilter/intro/
        $form->add($element);
        
        
        
        $viewModel = new ViewModel(['form' => $form]);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            //$language = new \Application\Entity\Language();
            $person = new \Application\Entity\Person();
            $form->bind($person);
            $form->setData($data);
            if (! $form->isValid()) {
                return $viewModel;
            }
            $em->persist($person);
            $em->flush();
            $this->flashMessenger()->addMessage("congratulations! you inserted an entity.");
            return $this->redirect()->toRoute('home');
        }
        return new ViewModel(['form'=>$form]);
    }
    
    public function otherTestAction()
    {
        $em      = $this->serviceManager->get('entity-manager');
        $form = new \Application\Form\Test($em);
        $viewModel = new ViewModel(['form' => $form]);
        $person = new \Application\Entity\Person();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->bind($person);
            $form->setData($data);
            if (! $form->isValid()) {
                return $viewModel;
            } else {
                echo "SHIT IS VALID ?!?";
                return $viewModel;
            }
        }
    }
}
