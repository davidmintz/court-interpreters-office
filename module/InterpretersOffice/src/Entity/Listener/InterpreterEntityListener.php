<?php /** module/InterpretersOffice/src/Entity/Listener/InterpreterEntityListener.php*/

namespace InterpretersOffice\Entity\Listener;

use InterpretersOffice\Entity\Interpreter;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;

use Zend\Log\LoggerAwareInterface;
use Zend\Log\LoggerInterface;

use SDNY\Vault\Service\Vault;

/**
 * Doctrine event listener for Interpreter entity
 * 
 * to be continued
 */
class InterpreterEntityListener implements EventManagerAwareInterface, LoggerAwareInterface
{
    
    use EventManagerAwareTrait;
    
    /**
     *
     * @var Zend\Log\LoggerInterface
     */
    protected $log;
    
    /**
     *
     * @var Vault
     */
    protected $vault;
    
    public function setLogger(LoggerInterface $log)
    {
        $this->log = $log;
    }
    
    public function setVaultService(Vault $vault)
    {
        $this->vault = $vault;
    }
    /**
     * callback 
     * 
     * runs when Interpreter entity is loaded
     * 
     * which in turn doesn't do anything
     * 
     * @param Interpreter $interpreter
     * @param LifecycleEventArgs $event
     */
    public function postLoad(Interpreter $interpreter, LifecycleEventArgs $event)
    {        

        $this->getEventManager()->trigger(__FUNCTION__, $this);
        //var_dump(is_null($this->log));
        $this->log->debug("this is ".__FUNCTION__ . " in your InterpreterEntityListener ...");
        //echo "Interpreter entity listener WTF? hello what the FUCKING FUCK??";

    }
    
    /**
     * callback 
     * 
     * runs when Interpreter entity is loaded
     * 
     * doesn't do anything at the moment, except trigger another event 
     * which in turn doesn't do anything
     * 
     * @param Interpreter $interpreter
     * @param LifecycleEventArgs $event
     */
    public function preUpdate(Interpreter $interpreter, LifecycleEventArgs $event)
    {        

        $this->getEventManager()->trigger(__FUNCTION__, $this);
        $this->log->debug("this is FUCKING ".__FUNCTION__ . " in your InterpreterEntityListener ...");

    }
}
