<?php

/**  module/InterpretersOffice/src/Service/Listener/AuthenticationListener */

namespace InterpretersOffice\Service\Listener;

use Laminas\EventManager\Event;
use Laminas\Log\Logger;
use Doctrine\ORM\EntityManager;
use InterpretersOffice\Entity\User;

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
     * @var string
     */
    const CHANNEL = 'security';

     /**
     * @var int $max_login_failures
     * 
     * Maximum number of consecutive login failures allowed before we
     * disable the user account.
     * 
     */
    protected $max_login_failures = 6;

    /**
     * constructor.
     *
     * @param Logger $log
     * @param EntityManager $entityManager
     * @param int $max_login_failures
     */
    public function __construct(Logger $log, EntityManager $entityManager, int $max_login_failures)
    {
        $this->log = $log;
        $this->entityManager = $entityManager;
        $this->max_login_failures = $max_login_failures;
    }

    /**
     * Event listener for user login success or failure.
     *
     * Records outcome of the authentication attempt in the log. On success,
     * updates the last_login field of the user entity. After $this->max_login_failures 
     * without a successful login, the account is disabled.
     *
     * @param Event
     *
     */
    public function onLogin(Event $e)
    {
        $params = $e->getParams();
        $result = $params['result'];
        $ip = \filter_input(\INPUT_SERVER, 'REMOTE_ADDR', \FILTER_VALIDATE_IP)
                ?: 'N/A';

        $user_entity = $result->getUserEntity(); 
        $security_warning = null;
        if ($result->isValid()) {
            $message = sprintf(
                'user %s authenticated from IP address: %s',
                $params['identity'],
                $ip
            );
            $user_entity->setLastLogin(new \DateTime())->setFailedLogins(0);

        } else {   // authentication failure
            if ($user_entity) { // they got half it right
                $user_entity->setFailedLogins(1 + $user_entity->getFailedLogins());
                if ($user_entity->getFailedLogins() >= $this->max_login_failures) {
                    // disable the account if it's not already disabled
                    if ($user_entity->isActive()) {
                        $user_entity->setActive(false);
                        $security_warning = "Max number of failed logins exceeded. Disabling account for  user {$user_entity->getUsername()}";
                    } else {
                        $security_warning = "Attempt to use disabled user account user {$user_entity->getUsername()}";
                    }
                }
            }          
            $message = sprintf('login failed for user %s from IP address %s, reason: %s',
                $params['identity'],$ip, json_encode($result->getMessages())
            );                        
        }
        $this->log->info($message, 
        ['channel'=>self::CHANNEL, 
        'entity_class' => User::class,'entity_id' => $user_entity ? $user_entity->getId() : null]);
        if ($security_warning) {
            $this->log->warn($security_warning, ['channel'=>self::CHANNEL, 
            'entity_class' => User::class,
            'entity_id' => $user_entity ? $user_entity->getId() : null,
            'ip' => $ip,
            ]);
        }
        $this->entityManager->flush();
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
        $this->log->info($message, ['channel'=>self::CHANNEL,'entity_class' => User::class,'entity_id' => $user ? $user->id : null]);
    }
}
