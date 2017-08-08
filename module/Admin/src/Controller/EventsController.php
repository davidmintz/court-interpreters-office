<?php
/**
 * module/Admin/src/Controller/LanguagesController.php.
 */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Doctrine\ORM\EntityManagerInterface;

use InterpretersOffice\Form;

/**
 *  EventsController
 */
class EventsController extends AbstractActionController
{

    /**
     * constructor
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }
    /**
     * index action
     *
     */
    public function indexAction()
    {

        return ['title' => 'events'];
    }

    /**
     * adds a new event
     *
     *
     */
    public function addAction()
    {
        $form = new Form\EventForm(
            $this->entityManager,
            ['action' => 'create']
        );
        $request = $this->getRequest();
        $form->setAttribute('action', $request->getRequestUri());

        $viewModel = (new ViewModel())
            ->setTemplate('interpreters-office/admin/events/form')
            ->setVariables([
                'title' => 'schedule | '.$this->params()->fromRoute('action'),
                'form'  => $form,
                ]);


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
            ['action' => 'create']
        );
        $request = $this->getRequest();
        $form->setAttribute('action', $request->getRequestUri());

        $viewModel = (new ViewModel())
            ->setTemplate('interpreters-office/admin/events/form')
            ->setVariables([
                'title' => $this->params()->fromRoute('action'),
                'form'  => $form,
                ]);


        return $viewModel;
    }
}
