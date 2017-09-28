<?php
/**
 * module/Admin/src/Controller/LanguagesController.php.
 */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Doctrine\ORM\EntityManagerInterface;
use Zend\Authentication\AuthenticationServiceInterface;

use InterpretersOffice\Admin\Form;

use InterpretersOffice\Entity;

/**
 *  EventsController
 */
class EventsController extends AbstractActionController
{
    
    /**
     * entity manager
     * 
     * @var EntityManagerInterface
     */
    protected $entityManager;
    
    /**
     * authentication service
     * 
     * @var AuthenticationServiceInterface 
     */
    protected $auth;
    
    /**
     * constructor
     *
     * @param EntityManagerInterface $em
     * @param AuthenticationServiceInterface $auth
     */
    public function __construct(EntityManagerInterface $em, 
            AuthenticationServiceInterface $auth)
    {
        $this->entityManager = $em;
        $this->auth = $auth;
    }
    /**
     * index action
     *
     */
    public function indexAction()
    {
       
        return ['title' => 'schedule'];
    }

    /**
     * adds a new event
     *
     *
     */
    public function addAction()
    {
        //scribble
        /*
        $entities = $this->entityManager->getRepository(Entity\Location::class)
                ->getChildren(1);
        foreach ($entities as $place) {
           // print_r(array_keys($place)); echo "... ";
            echo "({$place->getType()}) ",$place->getName(), '<br>';
        }*/
        $form = new Form\EventForm(
            $this->entityManager,
            [   'action' => 'create',
                'auth_user_role'=> $this->auth->getIdentity()->role,
                'object' => null,
            ]
        );

        $request = $this->getRequest();
        $form
             ->setAttribute('action', $request->getRequestUri());
        $event = new Entity\Event();
        $form->bind($event);
        $viewModel = (new ViewModel())
            ->setTemplate('interpreters-office/admin/events/form')
            ->setVariables([                
                'form'  => $form,
                ]);
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                echo "validation failed ... ";
                var_dump($form->getMessages());
                return $viewModel;
            } else {
                // faking some data for now
                echo "validation OK... ";
                $anonymousSubmitter = $this->entityManager->find(
                    Entity\Hat::class, 4
                );
                /*
                $user =  $this->entityManager->find(
                            Entity\User::class, 8
                        );
                 */
                //exit(get_class($user));
                $event->setAnonymousSubmitter($anonymousSubmitter);
                

                //\Doctrine\Common\Util\Debug::dump($event);

                $this->entityManager->persist($event);
                $this->entityManager->flush();
                echo "YAY!!!!!!";
            }
            
        }

        return $viewModel;
    }

    /**
     * edits an event
     *
     *
     */
    public function editAction()
    {
        $id = $this->params()->fromRoute('id');
        $event = $this->entityManager->find(Entity\Event::class,$id);
        if (! $event) {
             return (new ViewModel())
            ->setTemplate('interpreters-office/admin/events/form')
            ->setVariables([               
                'errorMessage'  => 
                "event with id $id was not found in the database."
             ]);
        }
        $form = new Form\EventForm(
            $this->entityManager,
            ['action' => 'update','object'=>$event,]
        );
        $request = $this->getRequest();
        $form->setAttribute('action', $request->getRequestUri());        
        $form->bind($event);
        
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                echo "yay!";
            } else {
                
            }
        }
        
        $viewModel = (new ViewModel())
            ->setTemplate('interpreters-office/admin/events/form')
            ->setVariables([               
                'form'  => $form,
             ]);

        return $viewModel;
    }
    
    /**
     * generates markup for an interpreter
     * 
     * @return Zend\Http\PhpEnvironment\Response
     * @throws \RuntimeException
     */
    public function interpreterTemplateAction()
    {
        $helper = new Form\View\Helper\InterpreterElementCollection();
        $factory = new \Zend\InputFilter\Factory();
        $inputFilter = $factory->createInputFilter(                
            $helper->getInputFilterSpecification()
        );
        $data = $this->params()->fromPost();
        $inputFilter->setData($data);
        if (! $inputFilter->isValid()) {
            throw new \RuntimeException(
                "bad input parameters: "
                    .json_encode($inputFilter->getMessages(),\JSON_PRETTY_PRINT)
            );
        }        
        $html = $helper->fromArray($data);
        return $this->getResponse()->setContent($html);
    }
    
    
    /**
     * gets interpreter options for populating select
     * 
     * @return JsonModel
     */
    public function interpreterOptionsAction()
    {
        /** @var  \InterpretersOffice\Entity\Repository\InterpreterRepository $repository */
        $repository = $this->entityManager->getRepository(Entity\Interpreter::class);        
        $language_id = $this->params()->fromQuery('language_id');
        if (! $language_id) {
            $result = ['error' => 'missing language id parameter'];
        } else {
            $result = $repository->getInterpreterOptionsForLanguage($language_id);
        }
        return new JsonModel($result);        
    }
}
