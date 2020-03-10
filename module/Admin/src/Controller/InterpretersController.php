<?php

/** module/Admin/src/Controller/InterpretersController */

namespace InterpretersOffice\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Admin\Form\InterpreterForm;
use InterpretersOffice\Entity;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Laminas\View\Model\JsonModel;
use InterpretersOffice\Admin\Form\InterpreterRosterForm;
use Laminas\Session\Container as Session;
use Laminas\Stdlib\Parameters;

/**
 * controller for admin/interpreters.
 * @todo DRY out the hydration/processing
 * @todo split off the read-only functions from the update/insert
 */
class InterpretersController extends AbstractActionController
{

    /**
     * whether our Vault module is enabled
     * @var boolean
     */
    protected $vault_enabled;

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
     * @param boolean $vault_enabled
     */
    public function __construct(EntityManagerInterface $entityManager, $vault_enabled = false)
    {

        $this->entityManager = $entityManager;
        $this->vault_enabled = $vault_enabled;
    }

    /**
     * display Interpreter details view
     *
     * to be implemented
     */
    public function viewAction()
    {
        //$id = $this->params()->fromRoute('id');
        return new ViewModel();
    }

    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {

        $autocomplete_term = $this->params()->fromQuery('term');
        if ($autocomplete_term) {
            return $this->autocomplete($autocomplete_term);
        }

        $params = $this->params()->fromRoute();
        $matchedRoute = $this->getEvent()->getRouteMatch();
        $routeName = $matchedRoute->getMatchedRouteName();
        $isQuery = ( 'interpreters' != $routeName );
        $form = new InterpreterRosterForm(['objectManager' => $this->entityManager]);
        $viewModel = new ViewModel([
            'title' => 'interpreters',
            //'objectManager' => $this->entityManager,
            ] + compact('form', 'params', 'isQuery', 'routeName'));
        if ('interpreters/find_by_id' == $routeName) {
            $viewModel->interpreter = $this->entityManager->find(
                Entity\Interpreter::class,
                $this->params()->fromRoute('id')
            );
        } else {
            if ($isQuery) {
                // i.e., there are search parameters in URL
                $viewModel->results = $this->find($params);
            }
        }
        return $this->initView($viewModel, $params, $isQuery);
    }

    /**
     * returns autocompletion data for name search textfield
     *
     * @param string $term
     * @return JsonModel
     */
    public function autocomplete($term)
    {

        $repository = $this->entityManager->getRepository('InterpretersOffice\Entity\Interpreter');
        return new JsonModel(
            $repository->autocomplete($term)
        );
    }

    /**
     * figures out appropriate defaults for interpreter roster search form
     *
     * @param ViewModel $viewModel
     * @param Array $params GET (route) parameters
     * @param boolean $isQuery whether submitting search terms, or just arriving
     *
     * @todo consider making this a method of the form instead
     */
    public function initView(ViewModel $viewModel, array $params, $isQuery)
    {

        $session = new Session('interpreter_roster');//$session->clear();return;

        if (! $isQuery) {
        // if no search parameters, get previous state from session if possible
            if ($session->params) {
                $viewModel->params = $session->params;
            }
        } else {
            // save search parameters in session for next time

            if (! empty($params['lastname'])) {
                $params['name'] = $params['lastname'];
                if (! empty($params['firstname'])) {
                    $params['name'] .= ", {$params['firstname']}" ;
                }
            }
            $session->params = $merged = array_merge($session->params ?: [], $params);
            $viewModel->params = $merged;
            //var_dump($session->params);
        }
        return $viewModel;
    }

    /**
     * finds interpreters
     *
     * gets interpreters based on search criteria. if we are given an id
     * parameter, find by id
     *
     * @param Array $params interpreter search parameters
     * @return array
     */
    public function find(array $params)
    {
        $repository = $this->entityManager
                ->getRepository(Entity\Interpreter::class);

        return $repository->search($params, $this->params()->fromQuery('page', 1));
    }

    public function availabilityListAction()
    {
        return false;
    }
}
