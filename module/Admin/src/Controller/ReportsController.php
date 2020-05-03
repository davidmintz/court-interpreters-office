<?php /** module/Admin/src/Controller/ReportsController.php  */

namespace InterpretersOffice\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
// use Laminas\View\Model\ViewModel;
// use Laminas\View\Model\JsonModel;
use Doctrine\ORM\EntityManagerInterface;

// use InterpretersOffice\Entity;

/**
 * reports controller
 *
 * a work in progress
 */
class ReportsController extends AbstractActionController
{

     /**
     * entity manager
     *
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SessionContainer
     *
     * session
     */
    private $session;


    /**
     * constructor
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->session = new \Laminas\Session\Container("reports");
    }

    /**
     * index page
     */
    public function indexAction()
    {
    }
}
