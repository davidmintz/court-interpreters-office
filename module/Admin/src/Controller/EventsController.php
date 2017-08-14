<?php
/**
 * module/Admin/src/Controller/LanguagesController.php.
 */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

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
     */
    public function __construct(EntityManagerInterface $em, AuthenticationServiceInterface $auth)
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
        $entities = $this->entityManager->getRepository('InterpretersOffice\Entity\Location')
                ->getChildren(1);
        foreach ($entities as $place) {
           // print_r(array_keys($place)); echo "... ";
            //echo "({$place->getType()}) ",$place->getName(), '<br>';
        }
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
                echo "validation OK";
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

        $form = new Form\EventForm(
            $this->entityManager,
            ['action' => 'update']
        );
        $request = $this->getRequest();
        $form->setAttribute('action', $request->getRequestUri());

        $viewModel = (new ViewModel())
            ->setTemplate('interpreters-office/admin/events/form')
            ->setVariables([               
                'form'  => $form,
             ]);

        return $viewModel;
    }
}
