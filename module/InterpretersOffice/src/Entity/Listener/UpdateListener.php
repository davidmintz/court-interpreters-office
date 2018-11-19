<?php
/**
 * module/InterpretersOffice/src/Entity/Listener/UpdateListener.php
 */
namespace InterpretersOffice\Entity\Listener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Common\EventSubscriber;
use InterpretersOffice\Entity\Repository\CacheDeletionInterface;
use InterpretersOffice\Entity\VerificationToken;
use InterpretersOffice\Entity\DefendantEvent;
use InterpretersOffice\Entity;
use InterpretersOffice\Requests\Entity\Request;
use InterpretersOffice\Module;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Log;
use InterpretersOffice\Service\Authentication\CurrentUserTrait;

/**
 * entity listener for clearing caches
 *
 * Interesting facts:  if you delete an InterpreterEvent without adding any,
 * the postRemove event is triggered; if you add an InterpreterEvent without
 * removing any, the prePersist event is triggered; if you REPLACE, i.e.,
 * both and remove InterpreterEvent entities, then the postUpdate event is
 * triggered -- but neither prePersist nor postRemove events are triggered
 *
 */
class UpdateListener implements EventSubscriber, Log\LoggerAwareInterface
{

    use Log\LoggerAwareTrait;
    use CurrentUserTrait;

    /**
     * current datetime
     *
     * @var \DateTime
     */
    protected $now;

    /**
     * auth service
     *
     * @var AuthenticationServiceInterface;
     */
    protected $auth;




    /**
     * the entity on which we are operating
     *
     * @var InterpretersOffice\Entity
     */
    private $entity;

    /**
     * sets authentication service
     *
     * @param AuthenticationServiceInterface $auth
     * @return UpdateListener
     */
    public function setAuth(AuthenticationServiceInterface $auth)
    {
        $this->auth = $auth;

        return $this;
    }

    /**
     * gets current datetime
     *
     * @return \DateTime
     */
    protected function getTimeStamp()
    {
        if (! $this->now) {
            $this->now = new \DateTime();
        }
        return $this->now;
    }

    /**
     * implements EventSubscriber
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return ['postUpdate','postRemove','postPersist','prePersist','onFlush'];
    }

    public function onFlush (OnFlushEventArgs $args ) {
        //return;
        $this->logger->debug(__METHOD__);
        /** @var Doctrine\ORM\UnitOfWork $uow */
        $uow = $args->getEntityManager()->getUnitOfWork();
        //$shit = print_r(get_class_methods($uow),true);
        //$this->logger->debug($shit);
        $entities = $uow->getScheduledEntityUpdates();
        foreach ($entities as $request) {
            if (!$request instanceof Request) {
                return;
            }
            $event = $request->getEvent();
            if (! $event) { return; }
        }
        // $request is a Request entity
        $changeset = $uow->getEntityChangeSet($request);
        // $shit = print_r($changeset,true);
        // $this->logger->debug($shit);
        $updated_event = false;
        foreach ($changeset as $field => $values) {
            if ($field == 'time') {
                if ($values[0]->format('H:i')  != $values[1]->format('H:i')) {
                    $this->logger->debug("real time change noted in ".__METHOD__);
                    $event = $request->getEvent();
                    $event->setTime($request->getTime());
                    $this->logger->debug("reset time to: ".$event->getTime()->format('H:i'));
                    $updated_event = true;
                }
            }
        }
        $em = $args->getEntityManager();
        if ($updated_event) {
            $this->logger->debug("trying to update event id: ".$event->getId());
            $event->setTime($request->getTime());
            $uow->recomputeSingleEntityChangeSet(
                $em->getClassMetadata(get_class($event)),$event
            );
        }
    }

    /**
    * postPersist event handler
    *
    * @param LifecycleEventArgs $args
    * @return void
    */
    public function postPersist(LifecycleEventArgs $args)
    {
        $user = $this->getAuthenticatedUser($args);

        $this->logger->debug(
            sprintf(
                'user %s has inserted entity %s',
                $user ? $user->getUsername() : 'nobody',
                get_class($args->getObject())
            )
        );
        $this->clear_cache($args);
    }


    /**
    * postRemove event handler
    *
    * @param LifecycleEventArgs $args
    * @return void
    */
    public function postRemove(LifecycleEventArgs $args)
    {
        $this->logger->debug(
            sprintf(
                'user %s deleting entity %s',
                $this->getAuthenticatedUser($args)->getUsername(),
                get_class($args->getObject())
            )
        );
        $this->clear_cache($args);
    }

    /**
     * clears cache if possible
     *
     * @param LifecycleEventArgs $args
     * @return void
     */
    private function clear_cache(LifecycleEventArgs $args)
    {
        $class = get_class($args->getObject());
        $repository = $args->getEntityManager()->getRepository($class);
        if ($repository instanceof CacheDeletionInterface) {
            $repository->deleteCache();
            $this->logger->debug(
                sprintf('cleared cache on CacheDeletionInterface instance %s',
                    $class)
            );
        } else {
            $this->logger->debug(
                "$class does not implement CacheDeletionInterface, not clearing"
            );
        }
    }

    /**
     * postUpdate listener
     *
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function postUpdate(LifecycleEventArgs $args)
    {

        $user = $this->getAuthenticatedUser($args);
        $this->logger->debug(
            sprintf(
                'user %s updated entity %s',
                $user ? $user->getUsername() : 'nobody',
                get_class($args->getObject())
            )
        );
        $this->clear_cache($args);
    }

    /**
     * prePersist listener
     *
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $user = $this->getAuthenticatedUser($args);
        if ($entity instanceof Entity\InterpreterEvent) {
            $entity->setCreatedBy($user)->setCreated($this->getTimeStamp());
            $this->logger->debug(
            "set createdBy and timestamp on InterpreterEvent in ".__METHOD__);
        }

        $this->logger->debug(
            sprintf(
                '%s:  user %s is creating a new %s',
                __METHOD__,
                $user ? $user->getUsername() : 'nobody',
                get_class($args->getObject())
            )
        );
        //$this->clear_cache($args);
    }
}
