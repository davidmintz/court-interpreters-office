<?php
/**
 * module/Admin/src/Controller/LanguagesController.php.
 */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Doctrine\ORM\EntityManagerInterface;


/**
 *  EventsController
 */
class EventsController extends AbstractActionController
{
    public function __construct(EntityManagerInterface $em) {
        $this->entityManager = $em;
    }
}
