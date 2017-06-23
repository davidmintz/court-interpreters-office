<?php /** module/InterpretersOffice/src/Entity/Listener/InterpreterEntityListener.php*/

namespace InterpretersOffice\Entity\Listener;

use InterpretersOffice\Entity\Interpreter;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
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
     * Vault client
     * 
     * @var Vault
     */
    protected $vault;
    
    public function setLogger(LoggerInterface $log)
    {
        $this->log = $log;
    }
    
    /**
     * encrypted values
     * 
     * store here for later comparison
     * 
     * @var Array
     */
    protected $encrypted_values = [];
    
    /**
     * sets Vault client
     * 
     * @param Vault $vault
     */
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

        $this->encrypted_values = [
            'dob' => $interpreter->getDob(),
            'ssn' => $interpreter->getSsn(),
        ];
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
    public function preUpdate(Interpreter $interpreter, PreUpdateEventArgs $event)
    {        
        $this->getEventManager()->trigger(__FUNCTION__, $this);
        
        $this->log->debug(sprintf('getSsn() now returns %s', $interpreter->getSsn()));
        $this->log->debug(sprintf(
            'hasChangedField? %s', $event->hasChangedField('ssn') ? "yes":"no"
        ));
        if ($event->hasChangedField('ssn')) {
            $old_value = $event->getOldValue('ssn');
            $new_value = $event->getNewValue('ssn');
            // if there is NO old value, but there IS a new value, just encrypt it
            if ($new_value && ! $old_value ) {
                $this->log->debug("updating from null ssn to not-null?");
                $interpreter->setSsn($this->vault->encrypt($new_value));
            } elseif ($old_value && ! $new_value) {
                // pass
                $this->log->debug("updating from  not-null to null?");
            } else {
                // compare old value decrypted with new
                $this->log->debug("comparing old-decrypted to new");
                $decrypted_old_value = $this->vault->decrypt($old_value);
                if ($decrypted_old_value != $new_value) {
                    // it really changed. encrypt
                    $this->log->debug("...it really was updated");
                    $interpreter->setSsn($this->vault->encrypt($new_value));
                } else {
                    // not really modified.
                    $this->log->debug("NOT really updated, attempting to unset from changeset");
                    $changeset = $event->getEntityChangeSet();
                    unset($changeset['ssn']);
                }
            }
        }
    }
    
    public function prePersist(Interpreter $interpreter, LifecycleEventArgs $event) {
        
        $this->log->debug("this is ".__FUNCTION__);
        foreach (['dob','ssn'] as $field) {
            $getter = 'get'.lcfirst($field);
            $value = $interpreter->$getter();
            if ($value) {
                $setter = 'set'.lcfirst($field);
                $interpreter->$setter($this->vault->encrypt($value));
                $this->log->debug("we have encrypted $field in ".__FUNCTION__);
            }
        }        
    }
}
