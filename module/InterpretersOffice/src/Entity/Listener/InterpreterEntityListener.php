<?php /** module/InterpretersOffice/src/Entity/Listener/InterpreterEntityListener.php*/

namespace InterpretersOffice\Entity\Listener;

use InterpretersOffice\Entity\Interpreter;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;

/**
 * Doctrine event listener for Interpreter entity
 * 
 * to be continued
 */
class InterpreterEntityListener implements EventManagerAwareInterface
{
    
    use EventManagerAwareTrait;
    
    
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
    public function postLoad(Interpreter $interpreter, LifecycleEventArgs $event)
    {        

        $this->events->trigger(__FUNCTION__, $this);

    }
}
