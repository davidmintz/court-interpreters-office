<?php

/** module/Admin/src/Controller/DefendantsController */

namespace InterpretersOffice\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Stdlib\RequestInterface as Request;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use InterpretersOffice\Admin\Form\DefendantForm;
use InterpretersOffice\Entity\Defendant;

use Laminas\Session\Container as Session;

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
     * defendant repository
     *
     * @var Entity\Repository\DefendantRepository
     */
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
        $repository = $entityManager->getRepository(Entity\Defendant::class);
        $this->repository = $repository;
    }

    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $session = new Session('admin_defendants');
        if ($session->search_term) {
            $paginator = $this->repository->paginate($session->search_term, $session->page ?? 1);
            return ['paginator' => $paginator,'search_term' => $session->search_term];
        }
    }

    /**
     * adds a defendant-name entity to the database.
     */
    public function addAction()
    {
        $viewModel = new ViewModel();
        $form = new DefendantForm($this->entityManager, ['action' => 'create']);
        $viewModel->setVariables(['form' => $form, 'title' => 'add a defendant name']);
        /** @var Laminas\Http\PhpEnvironment\Request  $request */
        $request = $this->getRequest();
        $entity = new Entity\Defendant();
        $form->bind($entity);
        $xhr = false;
        if ($request->isXmlHttpRequest()) {
            $xhr = true;
            $viewModel->setTerminal(true)->setVariables(['xhr' => true,]);
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
                $this->flashMessenger()->addSuccessMessage(
                    sprintf(
                        'The defendant name <strong>%s %s</strong> has been added to the database',
                        $entity->getGivenNames(),
                        $entity->getSurnames()
                    )
                );
                if ($xhr) {
                    return new JsonModel(['id' => $entity->getId(),
                        'error' => null]);
                }
                $this->redirect()->toRoute('admin-defendants');
            } catch (UniqueConstraintViolationException $e) {
                $existing_entity = $this->entityManager
                        ->getRepository(Entity\Defendant::class)
                        ->findOneBy([
                            'surnames' => $entity->getSurnames(),
                            'given_names' => $entity->getGivenNames()]);

                 return $xhr ?
                    new JsonModel([
                        'status' => 'error',
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
        $request = $this->getRequest();
        $viewModel = new ViewModel();
        $id = $this->params()->fromRoute('id');
        $xhr = false;
        if ($request->isXmlHttpRequest()) {
            $xhr = true;
            $viewModel->setTerminal(true)->setVariables(['xhr' => true]);
        }
        $form = new DefendantForm($this->entityManager, ['action' => 'update']);
        $form->setAttribute('action', $this->getRequest()->getUriString());
        $entity = $this->entityManager->find(Entity\Defendant::class, $id);
        if (! $entity) {
            $message = "A defendant name with id $id was not found in the database.";
            if (! $xhr) {
                $this->flashMessenger()->addWarningMessage($message);
                return $this->redirect()->toRoute('admin-defendants');
            } else {
                return $viewModel->setVariables(
                    ['error_not_found' => $message,'form' => $form]
                );
            }
        }
        $form->bind($entity);
        // at least for now... ----------------------------------
        $container = $this->getEvent()->getApplication()->getServiceManager();
        $logger = $container->get('log');
        $this->repository->setLogger($logger);
        $listener = $container->get(Entity\Listener\EventEntityListener::class);
        if (! $listener->getLogger()) {
            $listener->setLogger($logger);
        }
        /////////               ----------------------------------
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
            if (! $form->isValid()) {
                return new JsonModel(['validation_errors' => $form->getMessages()]);
            }
            // do we have an existing match?
            $existing_name = $this->repository->findDuplicate($entity);
            $resolution = $form->get('duplicate_resolution')->getValue();
            // $event_id = $this->params()->fromQuery('event_id');
            // $this->entityManager->transactional(
            //     function($em) use ($entity, $input, $existing_name, $resolution, $event_id)
            //     {
            //         $result = $this->repository->updateDefendantEvents(
            //             $entity,
            //             $input->get('occurrences', []),                
            //             $existing_name,
            //             $resolution,
            //             $event_id   
            //         );
            //     }
            // );
            $result = $this->repository->updateDefendantEvents(
                $entity,
                $input->get('occurrences', []),                
                $existing_name,
                $resolution,
                $this->params()->fromQuery('event_id')
            );
            $context = $this->params()->fromQuery('context', 'defendants');
            if ("success" == $result['status'] && 'events' !== $context) {
                $this->flashMessenger()->addSuccessMessage(
                    "The defendant name <strong>$entity</strong> has been updated"
                );
            }
            return new JsonModel($result);
        }
        //  a temporary hack...  because a name might have >0 related Request entities,
        //  but no related Event entities
        /** @todo something better */
        return $viewModel->setVariables(
            ['form' => $form,
            'checked' => $request->getPost()->get('occurrences') ?: [],
            'has_related_entities' => count($occurrences) ? true : $this->repository->hasRelatedEntities($id),
            'id' => $id,
            'occurrences' => $occurrences]
        );
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
        $entity = $this->entityManager->find(Entity\Defendant::class, $id);
        if (! $entity) {
             return new JsonModel(['error' => 'database record not found. ']);
        }
        $form = new DefendantForm($this->entityManager, ['action' => 'update']);
        $form->setAttribute('action', $this->getRequest()->getUriString());
        $form->bind($entity)->setData($request->getPost());
        if (! $form->isValid()) {
            return new JsonModel(['validation_errors' => $form->getMessages()]);
        }
        try {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            return new JsonModel(['id' => $id,'error' => null, 'status' => 'success']);
        } catch (UniqueConstraintViolationException $e) {
            $existing_entity = $this->entityManager
                    ->getRepository(Entity\Defendant::class)
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

    /**
     * deletes a defendant
     *
     */
    public function deleteAction()
    {

        $request = $this->getRequest();

        if ($request->isPost()) {
            $id = $this->params()->fromRoute('id');
            $name = $this->params()->fromPost('name');
            $what = 'defendant name';

            $entity = $this->entityManager->find(Entity\Defendant::class, $id);

            return $this->delete(compact('entity', 'id', 'name', 'what'));
        }
    }
}
