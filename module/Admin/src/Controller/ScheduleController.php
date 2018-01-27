<?php
/**
 * module/Admin/src/Controller/ScheduleController.php
 */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Doctrine\ORM\EntityManagerInterface;
use Zend\Authentication\AuthenticationServiceInterface;

use Zend\EventManager\Event;

use InterpretersOffice\Admin\Form;

use InterpretersOffice\Entity;

/**
 * ScheduleController
 *
 */
class ScheduleController extends AbstractActionController
{
    protected $entityManager;

    public function __construct(EntityManagerInterface $e)
    {
        $this->entityManager = $e;
    }

    public function indexAction()
    {
        

    }
}
