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
use InterpretersOffice\Entity\DefendantName;

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
    protected $repository;

    use DeletionTrait;

    /**
     * constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $repository = $entityManager->getRepository(Entity\DefendantName::class);
        $this->repository = $repository;

    }

    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        //$query = $this->entityManager->createQuery();
        //echo get_class($query);
        //echo get_class($this->getRequest());//Zend\Http\PhpEnvironment\Request
        return new ViewModel();
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
     * for posting updates to an inexact-duplicate defendant name
     * in the events/form context
     *
     */
    public function updateExistingAction()
    {
        $request = $this->getRequest();
        if ($request->isXmlHttpRequest() && $request->isPost()) {
            return $this->postXhrUpdate($request);
        }
        return $this->redirect()->toRoute('admin-defendants');
    }
    /**
     * updates a defendant entity.
     */
    public function editAction()
    {
        // 21832 can be changed to Rios Lopez
        $request = $this->getRequest();
        $viewModel = (new ViewModel())
            ->setTemplate('interpreters-office/admin/defendants/form.phtml');
        $id = $this->params()->fromRoute('id');
        $xhr = false;
        if ($request->isXmlHttpRequest()) {
            $xhr = true;
            $viewModel->setTerminal(true)->setVariables(['xhr' => true]);
        }
        $form = new DefendantForm($this->entityManager,['action'=>'update']);

        $entity = $this->entityManager->find(Entity\DefendantName::class, $id);
        if (! $entity) {
            $message = "Defendant with id was $id not found in your database.";
            if (! $xhr) {
                $this->flashMessenger()->addErrorMessage($message);
                return $this->redirect()->toRoute('admin-defendants');
            } else {
                return $viewModel->setVariables(
                    ['error'=>$message,'status'=>'NOT FOUND','form' => $form]);
            }
        }
        $form->bind($entity);
        // at least for now...
        $container =  $this->getEvent()->getApplication()->getServiceManager();
        $logger = $container->get('log');
        $this->repository->setLogger($logger);
        $listener = $container->get('InterpretersOffice\Entity\Listener\EventEntityListener');
        if (! $listener->getLogger()) {
            $listener->setLogger($logger);
        }
        /////////
        $occurrences = $this->repository->findDocketAndJudges($id);
        if (count($occurrences) > 0) {
            $form->attachOccurencesValidator();
        }
        if ($request->isPost()) {

            $input = $request->getPost();
            if (null !== $input->get('duplicate_resolution_required')) {
                $form->attachDuplicateResolutionValidator();
            }
            $form->setData($input);
            if (!$form->isValid()) {
                return new JsonModel(['validation_errors'=>$form->getMessages()]);
            }
            try {
                // do we have an existing match?
                $existing_name = $this->repository->findDuplicate($entity);
                $resolution =  $form->get('duplicate_resolution')->getValue();
                $result = $this->repository->updateDefendantEvents(
                    $entity, $input->get('occurrences',[]), $existing_name,
                    $resolution);
            } catch (\Exception $e) {
                $result = ['message' => $e->getMessage(), 'status' => 'error'];
            }
            $context = $this->params()->fromQuery('context','defendants');
            if ("success" == $result['status'] && 'events' !== $context) {
                $this->flashMessenger()->addSuccessMessage(
                "The defendant name <strong>$entity</strong> name has been updated"
                );
            }
            return new JsonModel($result);
        }

        return $viewModel->setVariables(
            ['form' => $form,
            'checked' => $request->getPost()->get('occurrences') ?: [],
            'occurrences'=>$occurrences]);
    }

    /**
     * handles POST request to update entity
     *
     * this is for the event form and adding a new defendant name to
     * the database
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
            return new JsonModel(['id' => $id,'errors' => null, 'status'=>'success']);
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

    public function deleteAction()
    {

        $request = $this->getRequest();

        if ($request->isPost()) {
            $id = $this->params()->fromRoute('id');
            $name = $this->params()->fromPost('name');
            $what = 'defendant name';

            $entity = $this->entityManager->find(Entity\DefendantName::class, $id);

            return $this->delete(compact('entity','id','name','what'));
        }
    }
}
