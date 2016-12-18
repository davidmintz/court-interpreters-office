<?php 
/**  module/InterpretersOffice/src/Service/Listener/UserListener */

namespace InterpretersOffice\Service\Listener;

use Zend\EventManager\Event;
use Zend\Log\Logger;
use Doctrine\ORM\EntityManager;

/**
 * listener that observes authentication events.
 * 
 * maybe we will rename it to AuthenticationListener and create 
 * a different listener for user registration and password reset
 * events
 * 
 */ 

class UserListener
{

	/**
	 * log instance
	 * 
	 * @var Logger
	 */
	protected $log;

	/**
	 * entity manager
	 *
	 * @var EntityManager
	 */
	protected $entityManager;

	/**
	 * constructor
	 *
	 * @param Logger
	 * @param EntityManager
	 * 
	 */
	public function __construct(Logger $log, EntityManager $EntityManager)
	{

		$this->log = $log;
		$this->entityManager = $entityManager;
	}

    public function onTest(Event $e)
    {
        echo "Hello! This is the handler that fires on the ".$e->getName(). " event!<br>";
        
    }

    /**
     * event handler for user login
     * 
     * @param Event 
     */
    public function onLogin(Event $e)
    {
    	$user = $e->getParams()['user'];
    	$this->log->info(sprintf(
    		'user %s authenticated. IP address: %s',
    		$user->getPerson()->getEmail(),
    		isset($_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'N/A' 

    	));
    	/**
    	 * @todo add last_login prop to entity and update
    	 */ 
    }
}
