<?php /** module/Requests/src/Entity/Listener/RequestEntityListener.php */
namespace InterpretersOffice\Requests\Entity\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
// use Doctrine\ORM\Event\OnFlushEventArgs;

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
     * Yet to come up with a better way to detect whether Request.defendants
     * have been modified so we can update metadata.
     *
     * @param Entity\Request $request
     * @param LifecycleEventArgs $args
     */
    public function postLoad(Entity\Request $request, LifecycleEventArgs $args)
    {

        $log = $this->getLogger();
        $log->debug("postload in RequestEntityListener: saving deft state");
        $this->previous_defendants = $request->getDefendants()->toArray();

    }

    /**
     * prePersist callback
     *
     * @param  Entity\Request      $request
     * @param  LifecycleEventArgs $args
     * @return void
     */
    public function prePersist(Entity\Request $request, LifecycleEventArgs $args)
    {
        $now = new \DateTime();
        $user = $this->getAuthenticatedUser($args);
        $request->setCreated($now)->setModified($now)
                ->setCancelled(false)
                ->setModifiedBy($user);
        if (! $request->getSubmitter()) {
            $request->setSubmitter($this->getCurrentUserPerson($args));
        }
        //$this->getLogger()->debug("YES, set Request metadata in prePersist listener");
    }

    /**
     * postPersist callback
     *
     * @param  EntityRequest      $request
     * @param  LifecycleEventArgs $args
     * @return void
     */
    public function postPersist(Entity\Request $request, LifecycleEventArgs $args)
    {
        $this->getEventManager()->trigger(
            'create',
            $this,
            ['args' => $args,'entity' => $request]
        );
    }


    /**
     * preUpdate callback.
     *
     * updates the modified and modifiedBy fields if data was actually changed.
     *
     * @param  Entity\Request $request
     * @param  PreUpdateEventArgs $args
     */
    public function preUpdate(Entity\Request $request, PreUpdateEventArgs $args)
    {
        $really_modified = false;
        $fields_updated = array_keys($args->getEntityChangeSet());

        if (count($fields_updated) or $this->defendantsWereModified($request)) {
            $shit = print_r($fields_updated,true);
            $this->getLogger()->info(__METHOD__." is updating: $shit, setting metadata") ;
             $request->setModified(new \DateTime())
                 ->setModifiedBy($this->getAuthenticatedUser($args));
             $user = $this->getAuthenticatedUser($args)->getUsername();

        }
        // this SHIT DOES NOT WORK either.
        // may be that preUpdate is not called in time for this to work?
        // if ($this->defendantsWereModified($request)) {
        //
        //         $this->getLogger()->info(__METHOD__.":  defts where modified") ;
        //         $event = $request->getEvent();
        //         $this->getLogger()->info(__METHOD__.": event is an instance of ".get_class($event));
        //         if ($event) {
        //             $ours = $event->getDefendants()->toArray();
        //             $event->setComments(__METHOD__." did shit with this, ",date('r'));
        //             $match = ($ours == $this->previous_defendants) ? 'true':'false';
        //             $this->getLogger()->info(__METHOD__.": was our defts collection same as theirs before update? $match");
        //             if ($ours == $this->previous_defendants) {
        //                 $this->getLogger()->info(__METHOD__.": updating event-defendants!!");
        //                 foreach($event->getDefendants() as $n){
        //                     $event->removeDefendant($n);
        //                 }
        //                 //$event->removeDefendants($event->getDefendants());
        //                 //$event->addDefendants($request->getDefendants());
        //                 foreach ($request->getDefendants() as $n) {
        //                     $event->addDefendant($n);
        //                 }
        //             }
        //         }
        //
        // }
        // Request cancellation. Cancellation is in fact an update: the entity's
        // boolean $cancelled is set to true. But it is treated as its own
        // special case.
        /*
        if ($args->hasChangedField('cancelled') && $request->isCancelled()) {
            $event_name = 'cancel';
        } else {
            $event_name = 'update';
        }
        */
    }



    /**
     * was the Defendants collection actually updated?
     *
     * @param  Entity\Request $request
     * @return boolean
     */
    private function defendantsWereModified(Entity\Request $request)
    {

        $now = $request->getDefendants()->toArray();
        $then = $this->previous_defendants;

        return $now != $then;
    }
}
