<?php

/** module/Admin/src/Controller/DefendantsController */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Stdlib\RequestInterface as Request;
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
        echo get_class($this->getRequest());//Zend\Http\PhpEnvironment\Request
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
            $viewModel->setTerminal(true)->setVariables(['xhr' => true]);
        }
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                return $xhr ?
                    new JsonModel(['validation_errors' => $form->getMessages()])
                    : $viewModel;
            }
            try {
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
                if ($xhr) {
                    return new JsonModel(['id' => $entity->getId(),
                        'errors' => null]);
                }
                $this->flashMessenger()->addSuccessMessage(
                    sprintf(
                        'The defendant name <strong>%s %s</strong> has been added to the database',
                        $entity->getGivenNames(),
                        $entity->getSurnames()
                    )
                );
                $this->redirect()->toRoute('admin-defendants');
            } catch (UniqueConstraintViolationException $e) {
                $existing_entity = $this->entityManager
                        ->getRepository(Entity\DefendantName::class)
                        ->findOneBy([
                            'surnames' => $entity->getSurnames(),
                            'given_names' => $entity->getGivenNames()]);

                 return $xhr ?
                    new JsonModel([
                        'duplicate_entry_error' => true,
                        'existing_entity' => [
                            'surnames' => $existing_entity->getSurnames(),
                            'given_names' => $existing_entity->getGivenNames(),
                            'id' => $existing_entity->getId(),
                        ]])
                    :
                    $viewModel->setVariables(['duplicate_entry_error' => true,
                            'existing_entity' => $existing_entity]);
            }
        }

        return $viewModel;
    }

    /**
     * updates a defendant entity.
     */
    public function editAction()
    {
        $request = $this->getRequest();
        if ($request->isXmlHttpRequest() && $request->isPost()) {
            return $this->postXhrUpdate($request);
        }
        $viewModel = (new ViewModel())
                ->setTemplate('interpreters-office/admin/defendants/form.phtml')
                ->setVariable('title', 'edit a defendant name');
        $id = $this->params()->fromRoute('id');
        // to be continued
        return $viewModel;
    }
    /**
     * handles POST request to update entity
     *
     * @param Request $request
     * @return JsonModel
     */
    public function postXhrUpdate(Request $request)
    {

        $id = $this->params()->fromRoute('id');
        $entity = $this->entityManager->find(Entity\DefendantName::class, $id);
        if (! $entity) {
             return new JsonModel(['error' => 'database record not found. ']);
        }
        $form = new DefendantForm($this->entityManager, ['action' => 'update']);
        $form->bind($entity)->setData($request->getPost());
        if (! $form->isValid()) {
            return new JsonModel(['validation_errors' => $form->getMessages()]);
        }
        try {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            return new JsonModel(['id' => $id,'errors' => null]);
        } catch (UniqueConstraintViolationException $e) {
            $existing_entity = $this->entityManager
                    ->getRepository(Entity\DefendantName::class)
                    ->findOneBy([
                        'surnames' => $entity->getSurnames(),
                        'given_names' => $entity->getGivenNames()]);

             return new JsonModel([
                'duplicate_entry_error' => true,
                'existing_entity' => [
                    'surnames' => $existing_entity->getSurnames(),
                    'given_names' => $existing_entity->getGivenNames(),
                    'id' => $existing_entity->getId(),
                ]
             ]);
        }
    }
}
