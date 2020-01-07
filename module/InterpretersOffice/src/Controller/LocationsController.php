<?php
/**
 * module/InterpretersOffice/src/Controller/LocationsController.php
 */

namespace InterpretersOffice\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Doctrine\ORM\EntityManager;

/**
 * for fetching data to populate dropdown menus
 */
class LocationsController extends AbstractActionController
{

    /**
     * entity manager
     *
     * @var EntityManager
     */
    protected $em;

    /**
     * constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
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
     * gets child locations as JSON for populating select menu via xhr
     * @return JsonModel
     * @throws \RuntimeException
     */
    public function getChildrenAction()
    {
        $parent_id = $this->params()->fromQuery('parent_id');
        if (! $parent_id) {
            throw new \RuntimeException("missing required parent_id parameter");
        }
        $repo = $this->em->getRepository('InterpretersOffice\Entity\Location');
        $data = $repo->getChildLocationValueOptions($parent_id);

        return new JsonModel($data);
    }
}
