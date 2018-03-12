<?php
/** module/InterpretersOffice/src/Entity/Listener/EventEntityListener.php */
namespace InterpretersOffice\Entity\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;

use Zend\Log\LoggerAwareInterface;

use InterpretersOffice\Entity;
use Zend\Log;
use Zend\Authentication\AuthenticationServiceInterface;
use Doctrine\ORM\EntityManager;

/**
 * Event entity listener.
 * Responsible for making sure certain meta data elements are set correctly.
 * For cache-related functions see {@see UpdateListener}
 */
class InterpreterEventEntityListener implements EventManagerAwareInterface, LoggerAwareInterface
{
    use Log\LoggerAwareTrait;
    use EventManagerAwareTrait;

    /**
     * authentication service
     *
     * @var AuthenticationServiceInterface
     */
    protected $auth;


    /*
     * holds a copy of related entiti ids before update
     *
     * @var array
     */
    //protected $state_before = ['interpreter_ids' => [],];

    /**
     * constructor
     *
     * @param \DateTime
     */
    public function __construct()
    {
        $this->now = new \DateTime();
    }


    /**
     * sets authentication service
     *
     * @param AuthenticationServiceInterface $auth
     * @return EventEntityListener
     */
    public function setAuth(AuthenticationServiceInterface $auth)
    {
        $this->auth = $auth;
        return $this;
    }
    /**
     * postLoad callback
     *
     * @param Entity\Event $eventEntity
     * @param LifecycleEventArgs $event
     */

}
