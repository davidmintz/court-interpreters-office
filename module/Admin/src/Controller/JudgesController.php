<?php
/**
 * module/Admin/src/Controller/JudgesController.php.
 */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use InterpretersOffice\Admin\Form\JudgeForm;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity;

/**
 * JudgesController.
 */
class JudgesController extends AbstractActionController
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
     * @param ObjectManager $entityManager
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
        $judges = $this->entityManager
            ->getRepository('InterpretersOffice\Entity\Judge')->getList();

        return new ViewModel(['title' => 'judges', 'judges' => $judges, ]);
    }

    /**
     * add a new Judge.
     */
    public function addAction()
    {
        $viewModel = (new ViewModel())
            ->setTemplate('interpreters-office/admin/judges/form.phtml');
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
        $viewModel = (new ViewModel())
                ->setTemplate('interpreters-office/admin/judges/form.phtml')
                ->setVariables(['title' => 'edit a judge']);
        $id = $this->params()->fromRoute('id');
        if (! $id) { // get rid of this, since it will otherwise be 404?
            return $viewModel->setVariables(
                ['errorMessage' => 'invalid or missing id parameter']);
        }
        $entity = $this->entityManager
            ->find('InterpretersOffice\Entity\Judge', $id);
        if (! $entity) {
            return $viewModel
            ->setVariables(['errorMessage' => "judge with id $id not found"]);
        } else {
            $viewModel->id = $id;
        }
        $form = new JudgeForm($this->entityManager,
            ['action' => 'update', 'object' => $entity]);
        $form->bind($entity);
        $viewModel->form = $form;

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
}
