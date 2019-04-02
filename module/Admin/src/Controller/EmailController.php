<?php
/**
 * module/Admin/src/Controller/EmailController.php
 */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
//use Doctrine\ORM\EntityManagerInterface;
//use InterpretersOffice\Entity;
//use InterpretersOffice\Entity\Event;
//use Zend\Session\Container as Session;
use InterpretersOffice\Admin\Service\EmailService;

/**
 * EmailController
 *
 */
class EmailController extends AbstractActionController
{

    /**
     * email service
     *
     * @var EmailService
     */
    private $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function sendAction()
    {

        return false;
    }

}
