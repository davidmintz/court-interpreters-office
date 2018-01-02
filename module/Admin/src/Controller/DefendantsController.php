<?php

/** module/Admin/src/Controller/DefendantsController */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
//use InterpretersOffice\Form\PersonForm;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use InterpretersOffice\Admin\Form\DefendantForm;

/**
 * controller for admin/defendants.
 */
class DefendantsController extends AbstractActionController
{
    /**
     * entity manager.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
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
        return new ViewModel(['title' => 'defendants']);
    }

    /**
     * adds a Person entity to the database.
     */
    public function addAction()
    {
        $viewModel = (new ViewModel())
                ->setTemplate('interpreters-office/admin/defendants/form.phtml');
        $form = new DefendantForm($this->entityManager, ['action' => 'create']);
        $viewModel->setVariables(['form' => $form, 'title' => 'add a defendant name']);
        $request = $this->getRequest();
        $entity = new Entity\DefendantName();
        $form->bind($entity);
        $xhr = false;
        if ($request->isXmlHttpRequest()) {
            $xhr = true;
            $viewModel->setTerminal(true)->setVariables(['xhr'=>true]);
        }
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                return $xhr ? 
                    new JsonModel(['validation_errors'=>$form->getMessages()])
                    : $viewModel;
            }
            try {
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
                if ($xhr) {
                    return new JsonModel(['id'=>$entity->getId(),'errors'=> null]);
                }
                $this->flashMessenger()->addSuccessMessage(
                sprintf(
                    'The defendant name <strong>%s %s</strong> has been added to the database',
                    $entity->getGivenNames(), $entity->getSurnames())
                );
                $this->redirect()->toRoute('admin-defendants');

            } catch (UniqueConstraintViolationException $e) {
                $existing_entity =  $this->entityManager
                        ->getRepository(Entity\DefendantName::class)
                        ->findOneBy([
                            'surnames'=>$entity->getSurnames(),
                            'givenNames'=>$entity->getGivenNames()]);
                
                 return $xhr ? 
                        new JsonModel([
                            'duplicate_entry_error'=>true,
                            'existing_entity'=>[
                                'surnames'=>$existing_entity->getSurnames(),
                                'given_names'=>$existing_entity->getGivenNames(),
                                'id'=>$existing_entity->getId(),
                            ]                           
                        ])
                         : 
                        $viewModel->setVariables(['duplicate_entry_error'=>true,
                            'existing_entity'=>$existing_entity]);
            }
        }

        return $viewModel;
    }

    /**
     * updates a defendant entity.
     */
    public function editAction()
    {
        $viewModel = (new ViewModel())
                ->setTemplate('interpreters-office/admin/defendants/form.phtml')
                ->setVariable('title', 'edit a defendant name');
        $id = $this->params()->fromRoute('id');

        return $viewModel;
    }

}
