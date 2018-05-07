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
     * log
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

    /**
     * sets logger instance
     *
     * @param LoggerInterface $log
     */
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
     * doesn't really do anything at the moment.
     *
     *
     * @param Interpreter $interpreter
     * @param LifecycleEventArgs $event
     */
    public function postLoad(Interpreter $interpreter, LifecycleEventArgs $event)
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this);
    }

    /**
     * callback
     *
     * runs when Interpreter entity about to be updated
     *
     * @param Interpreter $interpreter
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Interpreter $interpreter, PreUpdateEventArgs $event)
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this);
        if (! $this->vault) {
            return;
        }
        //$this->log->debug(sprintf('getSsn() now returns %s', $interpreter->getSsn()));
        $this->log->debug(sprintf(
            'hasChangedField? %s',
            $event->hasChangedField('ssn') ? "yes" : "no"
        ));
        foreach (['dob','ssn'] as $prop) {
            if ($event->hasChangedField($prop)) {
                $old_value = $event->getOldValue($prop);
                $new_value = $event->getNewValue($prop);
                $setter = 'set'.lcfirst($prop);
                // if there is NO old value, but there IS a new value,
                // just encrypt it without further ado
                if ($new_value && ! $old_value) {
                    $this->log->debug("updating from null $prop to $new_value (not-null)?");
                    $setter = 'set'.lcfirst($prop);
                    $encrypted = $this->vault->encrypt($new_value);
                    $interpreter->$setter($encrypted);
                } elseif ($old_value && ! $new_value) {
                    // pass. nothing to encrypt.
                    $this->log->debug("updating from  not-null $prop  to null?");
                } else {
                    // compare old value ~decrypted~ with new
                    $this->log->debug("comparing old-decrypted $prop to new");
                    $decrypted_old_value = ! $old_value ? null : $this->vault->decrypt($old_value);
                    if ($decrypted_old_value != $new_value) {
                        // it really changed. encrypt
                        $this->log->debug("...and $prop really was updated");
                        $interpreter->$setter($this->vault->encrypt($new_value));
                    } else {
                        // not really modified.
                        $this->log->debug("$prop NOT really updated, resetting to old encrypted value");
                        $interpreter->$setter($old_value);
                    }
                }
            }
        }
    }

    /**
     * listener for prePersist event.
     *
     * if our Hashicorp Vault module is enabled, encrypt the Interpreter's
     * date-of-birth and social security number using our vault client.
     *
     * @param Interpreter $interpreter
     * @param LifecycleEventArgs $event
     *
     */
    public function prePersist(Interpreter $interpreter, LifecycleEventArgs $event)
    {

        if (! $this->hasVault()) {
            $this->log->info("no vault enabled, not encrypting any interpreter data");
            return;
        }
        // @todo throw Exception if they try to save w/o encryption??
        foreach (['dob','ssn'] as $field) {
            $getter = 'get'.lcfirst($field);
            $value = $interpreter->$getter();
            if ($value) {
                /* worth considering...
                if (! $this->hasVault()) {
                    throw new \RuntimeException(
                      "cannot save interpreter $field without encryption");
                }
                 */
                $setter = 'set'.lcfirst($field);
                $interpreter->$setter($this->vault->encrypt($value));
                $this->log->debug("we have encrypted $field in ".__FUNCTION__);
            }
        }
    }

    /**
     * is Vault enabled?
     *
     * @return boolean
     */
    public function hasVault()
    {
        return $this->vault ? true : false;
    }
}
