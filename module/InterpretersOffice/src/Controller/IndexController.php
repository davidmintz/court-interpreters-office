<?php
/**
 * module/InterpretersOffice/src/Controller/IndexController.php.
 */

namespace InterpretersOffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 *  IndexController.
 *
 *  Currently, just for making sure the application runs, basic routing is
 *  happening, service container is working, views are rendered, etc.
 */
class IndexController extends AbstractActionController
{


    /**
     * constructor arguments are temporary, for informal testing/experimenting.
     *
     * @param \InterpretersOffice\Form\Factory\AnnotatedEntityFormFactory $formFactory
     * @param EntityManagerInterface                                      $em
     */
    public function __construct() //$formFactory, $em
    {
        //$this->formFactory = $formFactory;
        //$this->em = $em;
    }

    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        //$connection = $this->em->getConnection();
        //$driver = $connection->getDriver()->getName();

        return new ViewModel();
    }


}
