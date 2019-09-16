<?php /** module/Admin/src/Controller/SearchController.php  */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Doctrine\ORM\EntityManagerInterface;

/**
 *
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
    }

    public function indexAction()
    {

    }

}
