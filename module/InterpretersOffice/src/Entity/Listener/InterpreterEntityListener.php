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
    
    //private $ssn_obscured = '*********';//
    //private $dob_obscured = '**/**/****//';
    
    /**
     * callback 
     * 
     * runs when Interpreter entity is loaded
     * 
     * this is experimental. there might be a better approach.
     * 
     * @param Interpreter $interpreter
     * @param LifecycleEventArgs $event
     */
    public function postLoad(Interpreter $interpreter, LifecycleEventArgs $event)
    {        

        if ($interpreter->getSsn()) {
           // $interpreter->setSsn($this->ssn_obscured);
        }
        if ($interpreter->getDob()) {
           // $interpreter->setDob($this->dob_obscured);
        }
        //if ($this->events) {
            // to do: make this work even in test environment
        $this->events->trigger(__FUNCTION__, $this);
        //}
        //printf("\nshit is STILL running in %s! yay!",__METHOD__);
    }
}
