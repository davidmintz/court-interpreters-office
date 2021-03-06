<?php

/** module/Admin/src/Controller/PeopleController */

namespace InterpretersOffice\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Paginator\Paginator;
use InterpretersOffice\Form\PersonForm;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Entity;

use Laminas\Session\Container as Session;

/**
 * controller for admin/people.
 */
class PeopleController extends AbstractActionController
{
    use DeletionTrait;

    /**
     * entity manager.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * form configuration
     *
     * @var Array
     */
    protected $formConfig;

    /**
     * session
     * @var Session
     */
    protected $session;

    /**
     * sets form configuration
     *
     * @param Array $config
     */
    public function setFormConfig(array $config)
    {
        $this->formConfig = $config;
    }

    /**
     * constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->session = new Session('people_index');
    }

    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {

        $get = $this->params()->fromQuery();
        if ($this->getRequest()->isXmlHttpRequest() && $get) {
            return $this->search();
        }
        $paginator = null;
        if ((! $get) && $this->session->defaults) {
            // not xhr, no query parameters, yes session parameters
            $paginator = $this->entityManager
                ->getRepository(Entity\Person::class)
                ->paginate($this->session->defaults);
        }
        $repo = $this->entityManager->getRepository(Entity\Hat::class);
        $opts = $repo->getHatOptions([Entity\Hat::ANONYMITY_NEVER,
            Entity\Hat::ANONYMITY_OPTIONAL]);
        return new ViewModel(
            [   'title' => 'people',
                'defaults' => $this->session->defaults,
                'options' => $opts,
                'paginator' => $paginator,
            ]
        );
        // for a vue.js learning exercise
        //->setTemplate('interpreters-office/admin/people/vue.phtml');
    }

    /**
     * autocompletion for people lookup
     *
     */
    public function autocompleteAction()
    {
        $repo = $this->entityManager->getRepository(Entity\Person::class);
        $term = $this->params()->fromQuery('term');
        $hat  = $this->params()->fromQuery('hat');
        $active = $this->params()->fromQuery('active');
        $value_column = $this->params()->fromQuery('value_column', 'id');
        $data = $repo->autocomplete($term, compact('hat', 'active', 'value_column'));

        return new JsonModel($data);
    }
    /**
     * adds a Person entity to the database.
     */
    public function addAction()
    {
        $viewModel = new ViewModel();
        $form = new PersonForm(
            $this->entityManager,
            ['action' => 'create','constrain_email' => false]
        );
        $viewModel->setVariables(['form' => $form, 'title' => 'add a person']);
        $request = $this->getRequest();
        $entity = new Entity\Person();
        $form->bind($entity);
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                return $viewModel;
            }
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            $this->flashMessenger()->addSuccessMessage(
                sprintf(
                    'The person <strong>%s %s</strong> has been added to the database',
                    $entity->getFirstname(),
                    $entity->getLastname()
                )
            );
            $this->redirect()->toRoute('people');
        }

        return $viewModel;
    }

    /**
     * updates a Person entity.
     */
    public function editAction()
    {
        $repo = $this->entityManager->getRepository(Entity\Person::class);
        $id = $this->params()->fromRoute('id');
        $result = $repo->findPerson($id);
        $viewModel = new ViewModel(['title' => 'edit a person']);
        if (! $result) {
            return $viewModel->setVariables(['errorMessage' => "person with id $id not found"]);
        }
        $person = $result[0];
        // judges and interpreters are special cases
        if (is_subclass_of($person, Entity\Person::class)) {
            return $this->redirectToFormFor($person);
        }
        $user = $result[1];
        if ($user) {
            return $this->redirect()
                ->toRoute('users/edit', ['id' => $user->getId()]);
        }

        $viewModel->id = $id;
        $form = new PersonForm($this->entityManager, ['action' => 'update','constrain_email' => false]);
        $form->bind($person);
        $has_related = $repo->hasRelatedEntities($id);
        if ($has_related) {
            $form->getInputFilter()->get('person')->get('hat')
            ->setRequired(false)->setAllowEmpty(true);
        }
        $viewModel->setVariables(['form' => $form,
            'has_related_entities' => $has_related ]);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                return $viewModel;
            }
            $this->entityManager->flush();
            $this->flashMessenger()
                  ->addSuccessMessage(sprintf(
                      'The person <strong>%s %s</strong> has been updated.',
                      $person->getFirstname(),
                      $person->getLastname()
                  ));
            $this->redirect()->toRoute('people');
        }

        return $viewModel;
    }

    /**
     * redirects to the page with specialized form for the Person subclass.
     *
     * When they load /admin/people/edit/xxx, Doctrine will try to find a Person
     * whose id is xxx even when the entity is subclassed -- e.g.,
     * is a Judge or Interpreter entity -- which would result in loading the wrong form.
     * Our front end should not expose this url, but if anyone should somehow stumble
     * into it or explicitly load the url, this is how we handle it. our routing
     * and class-naming conventions make it possible to compute the url to which
     * we should redirect.
     *
     * @param Entity\Person $entity
     */
    public function redirectToFormFor(Entity\Person $entity)
    {
        $class = get_class($entity);
        $base = substr($class, strrpos($class, '\\') + 1);
        $route = strtolower($base).'s/edit';
        $this->redirect()->toRoute($route, ['id' => $entity->getId()]);
    }

    /**
     * returns names and ids of people of a particular "hat"
     *
     * @return JsonModel
     */
    public function getAction()
    {
        $repo = $this->entityManager->getRepository(Entity\Person::class);
        $hat_id = $this->params()->fromQuery('hat_id');
        $person_id = $this->params()->fromQuery('person_id');
        $data = $repo->getPersonOptions($hat_id, $person_id);

        return new JsonModel($data);
    }


    /**
     * search
     */
    public function search()
    {
        $repo = $this->entityManager->getRepository(Entity\Person::class);
        $params = $this->params()->fromQuery();
        //print_r($params); exit();
        $this->session->defaults = $params;
        /** @var \Laminas\Paginator\Paginator $paginator */
        $paginator = $repo->paginate($params);
        $view = (new ViewModel())
            ->setTerminal(true)
            ->setTemplate('people/results');

        return $view->setVariables(['paginator' => $paginator]);
    }

    /**
     * deletes a person
     * @todo log it
     * @return JsonModel
     */
    public function deleteAction()
    {

        $request = $this->getRequest();

        if ($request->isPost()) {
            $id = $this->params()->fromRoute('id');
            $name = $this->params()->fromPost('name');
            $what = 'person';
            $entity = $this->entityManager->find(Entity\Person::class, $id);

            return $this->delete(compact('entity', 'id', 'name', 'what'));
        }
    }

    /**
     * displays Person details
     *
     * @return ViewModel
     */
    public function viewAction()
    {
        $id = $this->params()->fromRoute('id');
        $class = $this->params()->fromRoute('class', 'person');
        $entity = null;
        switch ($class) {
            case 'person':
                $repo = $this->entityManager->getRepository(Entity\Person::class);
                $template = 'people/view';
                break;
            case 'judge':
                $repo = $this->entityManager
                ->getRepository(Entity\Judge::class);
                $template = 'judges/view';
                break;
            case 'user':
                $repo = $this->entityManager
                ->getRepository(Entity\User::class);
                $template = 'users/view';
                break;
        }
        $entity = $repo->view($id);
        $view = new ViewModel(compact('id', 'class', 'entity'));
        $view->setTemplate($template);

        return $view;
    }
}
