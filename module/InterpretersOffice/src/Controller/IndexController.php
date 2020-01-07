<?php
/**
 * module/InterpretersOffice/src/Controller/IndexController.php.
 */

namespace InterpretersOffice\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

/**
 *  IndexController.
 */
class IndexController extends AbstractActionController
{

    /**
     * constructor
     *
     */
    public function __construct()
    {
    }

    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        return new ViewModel();
    }

    /**
     * index action.
     *
     * @return ViewModel
     */
    public function contactAction()
    {
        return new ViewModel();
    }
}
