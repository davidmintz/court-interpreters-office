<?php

/**  module/InterpretersOffice/src/Service/Listener/AuthenticationListener */

namespace InterpretersOffice\Service\Listener;

use Zend\EventManager\Event;
use Zend\Log\Logger;
use Doctrine\ORM\EntityManager;

/**
 * listener that observes user login attempts and logout.
 */
class AuthenticationListener
{
    /**
     * log instance.
     *
     * @var Logger
     */
    protected $log;

    /**
     * entity manager.
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * constructor.
     *
     * @param Logger
     * @param EntityManager
     */
    public function __construct(Logger $log, EntityManager $entityManager)
    {
        $this->log = $log;
        $this->entityManager = $entityManager;
    }

    /**
     * event handler for user login success or failure.
     *
     * @param Event
     */
    public function onLogin(Event $e)
    {
        $params = $e->getParams();
        $result = $params['result'];
        $ip = \filter_input(\INPUT_SERVER, 'REMOTE_ADDR', \FILTER_VALIDATE_IP) ?: 'N/A';
        if ($result->isValid()) {
            $message = sprintf(
                'user %s authenticated from IP address: %s',
                $params['identity'],
                $ip
            );
            /*
            * @todo add last_login prop to entity ($result->getIdentity()) and update
            */
        } else {
            $message = sprintf(
                'login failed for user %s from IP address %s, reason: %s',
                $params['identity'],
                $ip,
                json_encode($result->getMessages())
            );
        }
        $this->log->info($message);
    }

    /**
     * event listener for user logout.
     *
     * @param Event
     */
    public function onLogout(Event $e)
    {
        $user = $e->getParam('user');
        $message = sprintf('user %s logged out', $user->email);
        $session = new \Zend\Session\Container('Authentication');
        $session->role = 'anonymous';
        $this->log->info($message);
    }
}
