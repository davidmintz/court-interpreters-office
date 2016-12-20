<?php

/** module/Admin/src/Controller/PeopleController */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use InterpretersOffice\Form\PersonForm;

use Doctrine\ORM\EntityManagerInterface;

/**
 * controller for admin/people.
 */
class PeopleController extends AbstractActionController
{
    
    /**
     * entity manager.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
    
    public function addAction()
    {
        $view = (new ViewModel())
                ->setTemplate('interpreters-office/admin/people/form.phtml');
        
        $form = new PersonForm($this->entityManager);
        $view->setVariables(['form'=>$form,'title'=>'add a person']);
        return $view;
    }
}
