<?php /** module/InterpretersOffice/src/Service/AccountManager.php */

namespace InterpretersOffice\Service;

use Zend\Mail;
use Zend\View\ViewModel;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;


/**
 * manages user account service
 */
class AccountManager
{
    use EventManagerAwareTrait;
    
    /**
     * constructor
     */
    public function __construct()
    {
    }
}
