<?php
/**
 * module/Application/src/Controller/IndexController.php.
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Interop\Container\ContainerInterface;

/**
 *  IndexController.
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
    //protected $serviceManager;

    /**
     * constructor.
     *
     * @see Application\Controller\Factory\IndexControllerFactory
     *
     * @param ContainerInterface $serviceManager
     */
    
    protected $formFactory;

    protected $em;
    
    /**
     * 
     * @param \Application\Form\Factory\AnnotatedEntityFormFactory $formFactory
     * @param type $em
     */
    public function __construct($formFactory, $em)
    {
        //$this->serviceManager = $serviceManager;
        $this->formFactory = $formFactory; 
        $this->em = $em;
    }


    /**
     * index action.
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
        $service = new \Zend\Authentication\AuthenticationService;
        //echo get_class($shit)," ... ";
        if ($service->hasIdentity()) {
            //require('module/Application/src/Entity/Role.php');
            
        
            $shit = $service->getStorage()->read();
        
            echo gettype($shit). "  is the type returned by read() ...<br>";
            echo  "YES identity ";
            $id = $service->getIdentity()->getId();
            //$user = $shit;
            $user = $this->em->find('Application\Entity\User',$id);
            echo gettype($user). " is the data type... ";
            echo get_class($user);
            echo "...",$user->getUsername();
            $person = $user->getPerson();
        
            echo " last name is ",$person->getLastname(), " role is ",
                $user->getRole()->getName(), " hat is ",$person->getHat();
        } else {
            echo " NOT authenticated... ";
        }
            
      
        
        $em = $this->em;
        //$thing = new \Application\Form\TestFieldset();
        //$thing->setObjectManager($em);
        // http://stackoverflow.com/questions/12002722/using-annotation-builder-in-extended-zend-form-class/18427685#18427685
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
        //http://stackoverflow.com/questions/29335878/zend-framework-2-form-issues-using-doctrine-as-a-hydrator
        //  you should invoke setHydrator() on form itself after adding the base fieldset.
        
        $form = $builder->createForm(\Application\Entity\Person::class);
        $form->setHydrator(new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($em));
        // the firstname, middlename and lastname elements have already been
        // added and configured.
        // this demonstrates that we can add more after the fact
        $element = new \DoctrineModule\Form\Element\ObjectSelect('hat',
        [
                    'object_manager' => $em,
                    'target_class' => 'Application\Entity\Hat',
                    'property' => 'name',
                    'label' => 'hat',
                    'display_empty_item' => true,
        ]);
        $filter = $form->getInputFilter();
        //\Zend\Debug\Debug::dump(get_class_methods($filter));
        $filter->add([
            'name' => 'hat',
            'validators' => [
                [
                    'name' => 'Zend\Validator\NotEmpty',
                    'options' => [
                        'messages' => [
                            'isEmpty' => 'the shit is empty, yo!',
                        ],
                    ],
                ],
            ],
        ]);
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
            if (!$form->isValid()) {
                return $viewModel;
            }
            $em->persist($person);
            $em->flush();
            $this->flashMessenger()->addMessage('congratulations! you inserted an entity.');

            return $this->redirect()->toRoute('home');
        }

        return new ViewModel(['form' => $form]);
    }
    /**
     * temporary; for doodling and experimenting.
     *
     * @return ViewModel
     */
    public function otherTestAction()
    {
        $form = new \Zend\Form\Form('person-form');
        $em = $this->serviceManager->get('entity-manager');
        $hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($em);
        $form->setHydrator($hydrator);

        $fieldset = new \Application\Form\PersonFieldset($em);
        $person = new \Application\Entity\Person();
        $fieldset->setObject($person)->setHydrator($hydrator);
        $form->add($fieldset);
        $viewModel = new ViewModel(['form' => $form]);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->bind($person);
            $form->setData($data);
            if (!$form->isValid()) {
                return $viewModel;
            } else {
                echo 'SHIT IS VALID ?!?';
                try {
                    $em->persist($person);
                    $em->flush();
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }

                return $viewModel;
            }
        }

        return $viewModel;
    }
    /*
        $em      = $this->serviceManager->get('entity-manager');
        $form = new \Application\Form\Test($em);

        //echo "ok."; return new ViewModel;

        $viewModel = new ViewModel(['form' => $form]);
        $hat = new \Application\Entity\Hat();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $form->bind($hat);
            $form->setData($data);
            if (! $form->isValid()) {
                return $viewModel;
            } else {
                echo "SHIT IS VALID ?!?";
                return $viewModel;
            }
        }
    */
    
}


