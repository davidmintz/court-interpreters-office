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

use InterpretersOffice\Admin\Service\DefendantNameService;

use Laminas\Session\Container as Session;

/**
 * controller for admin/defendants.
 * 
 * currently this is a little weird and inconsistent because before, the entity was bound to a form. later
 * we decided a different approach would be better:  introduce a Service class and hydrate things 
 * ourselves, manually, so we could handle the complexities in a discreet, separate class. so in 
 * the case where they are updating/inserting names from the /admin/defendants route, we go that way. 
 * but when they edit a name in the /admin/schedule/(edit|add) context, we're still handling the same way 
 * as before.
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
     * preserves the search state in the session for their convenience when they come back.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $session = new Session('admin_defendants');
        if ($session->search_term) {
            $paginator = $this->repository->paginate($session->search_term, $session->page ?? 1);
            $data = ['paginator' => $paginator,'search_term' => $session->search_term];
        }
        $viewModel = new ViewModel($data??[]);
        if ($this->getRequest()->isXmlHttpRequest()) {
            $viewModel->setTerminal(true)
                ->setTemplate('interpreters-office/defendants/search')->search =  $session->search_term;
        }
        return $viewModel;
    }

    /**
     * adds a Defendant (name) entity
     */
    public function addAction()
    {
        $form = new DefendantForm(['action' => 'create']);
        if ($this->getRequest()->isPost()) {
            return $this->postInsert($form);
        }
        return ['form' => $form, 'title' => 'add a defendant name', 
        'xhr' => $this->getRequest()->isXmlHttpRequest()];
    }

    /**
     * processes POST data for an insert
     */
    protected function postInsert(DefendantForm $form)
    {
        $form->setData($this->getRequest()->getPost());
        if (! $form->isValid()) {
            return new JsonModel(['validation_errors' => $form->getMessages()]);
        }
        $service = new DefendantNameService($this->entityManager);
        $return = $service->insert($form->getData());
        
        return new JsonModel($return);
    }

    /**
     * processes POST data for an update
     */
    protected function postUpdate(DefendantForm $form, Entity\Defendant $entity)
    {
        $service = new DefendantNameService($this->entityManager);
        $data = $this->getRequest()->getPost()->toArray();
        $id = $this->params()->fromRoute('id');

        $form->setData($this->getRequest()->getPost());
        if (! $form->isValid()) {
            $result = [ 'validation_errors' => $form->getMessages()];            
        } else {
            $result = $service->update($entity,$data);
            if (!isset($result['status']) or $result['status'] == "error") {
                $this->getResponse()->setStatusCode(500);                
            }
        }

        return new JsonModel($result);
    }

    /**
     * edits defendant name
     */
    public function editAction()
    {
        $form = new DefendantForm(['action' => 'update']);        
        $id = $this->params()->fromRoute('id');
        $xhr = $this->getRequest()->isXmlHttpRequest();
        $entity = $this->entityManager->find(Entity\Defendant::class, $id);
        if (! $entity) {
            $message = "A defendant name with id $id was not found in the database.";
            if (! $xhr) {
                // the context is admin/defendants
                $this->flashMessenger()->addWarningMessage($message);
                return $this->redirect()->toRoute('admin-defendants');
            } else {
                // the context is admin/schedule/(edit|add)
                return  ['error_not_found' => $message,'form' => $form, 'id'=>$id];            
            }
        }
        $contexts = $this->repository->findDocketAndJudges($id);
        if ($contexts) {
            $form->attachContextsValidator();
        }
        if ($this->getRequest()->isPost()) {
            return $this->postUpdate($form,$entity);
        }
        // we are a GET, so display the form, possibly with context/radio buttons        
        $form->setData(['given_names'=>$entity['given_names'],
            'surnames'  => $entity['surnames'], 'id' => $id,
        ]);
        return (new ViewModel(['form' => $form, 'id' => $id,  'contexts' => $contexts,
        'has_related_entities' => count($contexts) ? true : 
            $this->repository->hasRelatedEntities($id),            
        'xhr' => $xhr,]))->setTerminal(true);
    }

    /**
     * for posting updates to an inexact-duplicate defendant name
     * in the events/form context
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
     * handles POST request to update entity
     *
     * for the event form, when adding|updating a defendant name
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
            /** note to self: maybe better to use the Service?  */
            $this->entityManager->persist($entity); // may be necessary, or not
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
