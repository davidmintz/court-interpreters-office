<?php /** module/Admin/src/Controller/SearchController.php  */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Admin\Form\SearchForm;
use InterpretersOffice\Entity;

/**
 * search controller
 */
class SearchController extends AbstractActionController
{

    /**
     * entity manager
     *
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * constructor
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->session = new \Zend\Session\Container("event_search");
    }

    public function indexAction()
    {
        $docket = $this->params()->fromRoute('docket');
        $query = $this->params()->fromQuery();
        if ($docket) {

        }
        $form = new SearchForm($this->em);
        $page = (int)$this->params()->fromQuery('page',1);
        $repository = $this->em->getRepository(Entity\Event::class);
        //exit("fuck me");
        if (!$query) {
            if ($this->session->search_defaults) {
                $defaults = $this->session->search_defaults;
                $params = $defaults;
                $form->setData($params);
                $results = $repository->search($params,$defaults['page']);
            } else {
                $results = null;
            }
            return new ViewModel(compact('form','results'));
        }
        // else, we have form/query data to validate
        $form->setData($query);
        if (!$form->isValid()) {
            $response = [
                'valid' => false,
                'validation_errors' => $form->getMessages(),
            ];
            return new JsonModel($response);
        }
        // all good
        $form_values = $form->getData();
        $form_values['page'] = $page;
        $this->session->search_defaults = $form_values;
        $results = $repository->search($form_values,$page);
        $view = new ViewModel(compact('results'));
        $is_xhr = $this->getRequest()->isXmlHttpRequest();
        if (!$is_xhr) {
            $view->setVariables(['form' => $form,]);
        } else {
            $view->setTemplate('search/results')
                 ->setTerminal(true);
        }

        return $view;
    }

}
