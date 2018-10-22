<?php
/**
 * module/InterpretersOffice/src/Entity/Listener/UpdateListener.php
 */
namespace InterpretersOffice\Entity\Listener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\Common\EventSubscriber;
use InterpretersOffice\Entity\Repository\CacheDeletionInterface;
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
        return ['postUpdate','postRemove','postPersist','prePersist',];
    }


    /**
    * postPersist event handler
    *
    * @param LifecycleEventArgs $args
    * @return void
    */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->logger->debug(
            sprintf(
                'user %s inserting entity %s',
                $this->getAuthenticatedUser($args)->getUsername(),
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
                sprintf('cleared cache on CacheDeletionInterface instance %s',$class)
            );
        } else {
            $this->logger->debug(
                "$class is not an implentation of CacheDeletionInterface, not clearing cache"
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
                $user->getUsername(),
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
        if ($entity instanceof Entity\InterpreterEvent) {
            $this->logger->debug(__METHOD__.':  interp_event is being created, updating event');
            $entity->getEvent()->setModified($this->getTimeStamp());
            $user = $this->getAuthenticatedUser($args);
            $entity->setCreatedBy($user)->setCreated($this->getTimeStamp());
            $this->logger->debug("set createdBy and timestamp on InterpreterEvent here in ".__METHOD__);
        } elseif ($entity instanceof Request) {
            $now = $this->getTimeStamp();
            $user = $this->getAuthenticatedUser($args);
            $person = $this->getCurrentUserPerson($args);
            $entity->setCreated($now)
                ->setModified($now)
                ->setSubmitter($person)
                ->setModifiedBy($user);
        }
        $this->logger->debug(
            sprintf(
                'user %s created entity %s',
                $this->getAuthenticatedUser($args)->getUsername(),
                get_class($args->getObject())
            )
        );
        $this->clear_cache($args);
    }
}
