<?php
/**
 * module/Admin/src/Controller/JudgesController.php.
 */

namespace InterpretersOffice\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use InterpretersOffice\Admin\Form\JudgeForm;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity;
use Laminas\Session\Container as Session;

/**
 * JudgesController.
 */
class JudgesController extends AbstractActionController
{
    use DeletionTrait;

    /**
     * entity manager.
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * session
     *
     * For persisting the state of the judges index
     * page
     *
     * @var Laminas\Session\Container
     */
    private $session;

    /**
     * constructor.
     *
     * @param ObjectManager $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * gets session
     *
     * @return Session
     */
    private function getSession()
    {
        if (! $this->session) {
            $this->session = new Session("judges_list");
        }
        return $this->session;
    }

    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {

        $display = $this->params()->fromQuery('display');
        if (! $display) {
            $display = $this->getSession()->display ?: 'active';
        } else {
             $this->getSession()->display = $display;
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            return new JsonModel(['status' => 'OK','display' => $display]) ;
        }
        $judges = $this->entityManager
            ->getRepository(Entity\Judge::class)->getList();

        return new ViewModel(['title' => 'judges', 'judges' => $judges, 'display' => $display]);
    }
    /**
     * view judge details
     *
     * @return ViewModel|array
     */
    public function viewAction()
    {
        $id = $this->params()->fromRoute('id');
        $repo = $this->entityManager
            ->getRepository(Entity\Judge::class);

        return ['judge' => $repo->getJudge($id),'id' => $id];
    }

    /**
     * add a new Judge.
     */
    public function addAction()
    {
        $viewModel = new ViewModel();
        $form = new JudgeForm($this->entityManager, ['action' => 'create']);
        $viewModel->setVariables(
            ['title' => 'add a judge', 'form' => $form]
        );

        $request = $this->getRequest();
        $hatRepository = $this->entityManager
            ->getRepository('InterpretersOffice\Entity\Hat');
        $hat = $hatRepository->getHatByName('Judge');
        $entity = new Entity\Judge($hat);
        $form->bind($entity);
        if ($request->isPost()) {//findOneBy(['name' => 'Judge']);
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                // echo "not valid.";  print_r($form->getMessages());
                return $viewModel;
            }
            $this->entityManager->persist($entity);

            $this->entityManager->flush();
            $this->flashMessenger()->addSuccessMessage(sprintf(
                'Judge <strong>%s %s, %s</strong> has been added to the database',
                $entity->getFirstname(),
                $entity->getLastname(),
                (string) $entity->getFlavor()
            ));
            $this->redirect()->toRoute('judges');
        }

        return $viewModel;
    }

    /**
     * updates a Judge entity.
     */
    public function editAction()
    {
        $viewModel = new ViewModel(['title' => 'edit a judge']);
        $id = $this->params()->fromRoute('id');
        $repo = $this->entityManager->getRepository(Entity\Judge::class);
        $entity = $repo->find($id);
        if (! $entity) {
            return $viewModel
            ->setVariables(['errorMessage' => "judge with id $id not found"]);
        } else {
            $viewModel->id = $id;
        }
        $form = new JudgeForm(
            $this->entityManager,
            ['action' => 'update', 'object' => $entity,]
        );
        $form->bind($entity);
        $has_related_entities = $repo->hasRelatedEntities($id);
        $viewModel->setVariables(compact('form', 'has_related_entities'));
        if ($has_related_entities) {
            $form->getInputFilter()->get('judge')->remove('flavor');
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                return $viewModel;
            }
            $this->entityManager->flush();
            $this->flashMessenger()
                  ->addSuccessMessage(sprintf(
                      'Judge <strong>%s %s, %s</strong> has been updated.',
                      $entity->getFirstname(),
                      $entity->getLastname(),
                      (string) $entity->getFlavor()
                  ));
            $this->redirect()->toRoute('judges');
        }

        return $viewModel;
    }


        /**
         * deletes a judge.
         *
         * @return JsonModel
         */
    public function deleteAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $id = $this->params()->fromRoute('id');
            $name = $this->params()->fromPost('name');
            $what = "Judge";
            $entity = $this->entityManager->find(Entity\Judge::class, $id);

            return $this->delete(compact('entity', 'id', 'name', 'what'));
        }
    }
}
