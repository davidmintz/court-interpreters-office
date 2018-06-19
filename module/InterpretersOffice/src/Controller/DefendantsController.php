<?php
/**
 * module/InterpretersOffice/src/Controller/DefendantsController.php
 */

namespace InterpretersOffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManager;

use InterpretersOffice\Entity;
use InterpretersOffice\Admin\Form\View\Helper\DefendantNameElementCollection
    as DeftNameHelper;

/**
 *
 * for fetching defendant data for autocompletion, etc
 */
class DefendantsController extends AbstractActionController
{

    /**
     * entity manager
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * view helper
     *
     * @var DeftNameHelper
     */
    protected $helper;

    /**
     * constructor
     *
     * @param EntityManager $em
     * @param DeftNameHelper $helper
     */
    public function __construct(EntityManager $em, DeftNameHelper $helper)
    {

        $this->entityManager = $em;
        $this->helper = $helper;
    }

    /**
     * don't really need this. it can be removed
     *
     * @return \InterpretersOffice\Controller\ViewModel
     */
    public function indexAction()
    {
        return $this->getResponse()
            ->setContent("LocationsController/indexAction works");
    }

   /**
     * autocompletion for the defendant-name search box in
     * the interpreter-request form
     */
    public function autocompleteAction()
    {
        $repo = $this->entityManager->getRepository(Entity\DefendantName::class);
        $term = $this->params()->fromQuery('term');
        $data = $repo->autocomplete($term);

        return new JsonModel($data);
    }

    /**
     * returns response containing defendant-name markup
     *
     * this is for invoking via javascript/xhr
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function templateAction()
    {
        $helper = $this->helper;
        $data = $this->params()->fromQuery();
        $html = $helper->fromArray($data);
        return $this->getResponse()->setContent($html);
    }

    /**
     * returns defendant-name search results
     *
     * @return ViewModel
     */
    public function searchAction()
    {
        $search = $this->params()->fromQuery('term');
        $repo = $this->entityManager->getRepository(Entity\DefendantName::class);
        $paginator = $repo->paginate($search, $this->params()->fromQuery('page'));
        $viewModel = new ViewModel(['paginator' => $paginator,'search' => $search]);
        $request = $this->getRequest();
        if ($request->isXmlHttpRequest()) {
            $viewModel->setTerminal(true);
        }

        return $viewModel;
    }
}
