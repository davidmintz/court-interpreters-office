<?php /** module/Requests/src/Entity/Listener/RequestEntityListener.php */
namespace InterpretersOffice\Requests\Entity\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\Log\LoggerAwareInterface;

use InterpretersOffice\Requests\Entity;
use Zend\Log;
use Zend\Authentication\AuthenticationServiceInterface;
use InterpretersOffice\Service\Authentication\CurrentUserTrait;
use Doctrine\ORM\EntityManager;

/**
 * Request entity listener.
 */
class RequestEntityListener implements EventManagerAwareInterface, LoggerAwareInterface
{
    use Log\LoggerAwareTrait;
    use EventManagerAwareTrait;
    use CurrentUserTrait;

    /**
     * authentication service
     *
     * @var AuthenticationServiceInterface
     */
    protected $auth;

    /**
     * array of defendant names for later comparison
     *
     * @var Array
     */
    protected $previous_defendants;

    /**
     * sets Authentication service
     *
     * @param AuthenticationServiceInterface $auth
     */
    public function setAuth(AuthenticationServiceInterface $auth)
    {
        $this->auth = $auth;

        return $this;
    }


    /**
     * postLoad callback
     *
     * @param Entity\Request $request
     * @param LifecycleEventArgs $args
     */
    public function postLoad(
        Entity\Request $request,
        LifecycleEventArgs $args
    ) {

        $log = $this->getLogger();
        $log->debug("postload callback running in Request entity listener");
        $this->previous_defendants = $request->getDefendants()->toArray();
    }

    /**
     * prePersist callback
     *
     * @param  Entity\Request      $request
     * @param  LifecycleEventArgs $args
     * @return void
     */
    public function prePersist(Entity\Request $request,LifecycleEventArgs $args)
    {
        $now = new \DateTime();
        $user = $this->getAuthenticatedUser($args);
        $person = $this->getCurrentUserPerson($args);
        $request->setCreated($now)->setModified($now)
                ->setCancelled(false)
                ->setSubmitter($person)
                ->setModifiedBy($user);
        $this->getLogger()->debug("YES, we have set Request metadata in prePersist listener");
    }
    /**
     * postRemove callback
     *
     * @param  Entity\Request      $request
     * @param  LifecycleEventArgs $args
     * @return void
     * @todo do something
     */
    public function postRemove(Entity\Request $request,LifecycleEventArgs $args)
    {
        $user = $this->getAuthenticatedUser($args)->getUsername();
        $this->getLogger()->info("user $user is deleting a Request");
        // to be continued!!
    }

    /**
     * preUpdate callback.
     *
     * updates the modified and modifiedBy fields if data was actually changed.
     *
     * @param  Entity\Request $request
     * @param  PreUpdateEventArgs $args
     */
    public function preUpdate(Entity\Request $request,PreUpdateEventArgs $args)
    {
        $really_modified = false;
        $fields_updated = array_keys($args->getEntityChangeSet());
        if (array_diff($fields_updated,['date','time'])) {
            $really_modified = true;
            //$this->getLogger()->debug("fields OTHER THAN date|time were changed");
        } else {
            $time_before = $args->getOldValue('time')->format('H:i');
            $time_after = $args->getNewValue('time')->format('H:i');
            if ($time_before != $time_after) {
                $really_modified = true;
            } elseif ($args->getOldValue('date') != $args->getNewValue('date')) {
                $really_modified = true;
            }
        }

        if ($really_modified or $this->defendantsWereModified($request)) {
            //$this->getLogger()->debug("YES, updating request meta in preUpdate listener");
            $request->setModified( new \DateTime())
                ->setModifiedBy($this->getAuthenticatedUser($args));
            $user = $this->getAuthenticatedUser($args)->getUsername();
            $this->getLogger()->info("user $user (really) is updating a Request");
        }
        //  trigger event for the ScheduleListener, which was attached when we
        //  were instantiated
        $this->getEventManager()->trigger( __FUNCTION__, $this,
            ['args'=>$args,'entity'=>$request]
        );
    }

    /**
     * was the Defendants collection actually updated?
     *
     * @param  EntityRequest $request
     * @return boolean
     */
    private function defendantsWereModified(Entity\Request $request) {

        $now = $request->getDefendants()->toArray();
        $then = $this->previous_defendants;

        return $now != $then;

    }
}
