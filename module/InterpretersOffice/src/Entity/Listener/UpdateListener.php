<?php
/**
 * module/InterpretersOffice/src/Entity/Listener/UpdateListener.php
 */
namespace InterpretersOffice\Entity\Listener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use InterpretersOffice\Entity\Repository\CacheDeletionInterface;
use InterpretersOffice\Entity\InterpreterEvent;
use InterpretersOffice\Entity\Interpreter;
use InterpretersOffice\Entity\Event;
use InterpretersOffice\Service\Authentication\AuthenticationAwareInterface;
use Zend\Authentication\AuthenticationServiceInterface;

use InterpretersOffice\Entity;

use Zend\Log;
//use InterpretersOffice\Service\Authentication\CurrentUserTrait;

/**
 * entity listener for clearing caches and setting Event entity metadata
 *
 * Interesting facts:  if you delete an InterpreterEvent without adding any,
 * the postRemove event is triggered; if you add an InterpreterEvent without
 * removing any, the prePersist event is triggered; if you REPLACE, i.e.,
 * both and remove InterpreterEvent entities, then the postUpdate event is
 * triggered -- but neither prePersist nor postRemove
 *
 */
class UpdateListener implements EventSubscriber, Log\LoggerAwareInterface
    //AuthenticationAwareInterface
{

    use Log\LoggerAwareTrait;
    //use CurrentUserTrait;

    /*
     * auth
     *
     * @var AuthenticationServiceInterface
     */
    protected $auth;

    /**
     * the Event entity
     *
     * a reference to the current Entity\Event so we can set its metadata
     * on listeners that observe events in related entities.
     *
     * @var Entity\Event
     */
    protected $eventEntity;

    /**
     * entity state just after loading
     *
     * @var Array
     */
    protected $state_before = [];

    /**
     * current datetime
     *
     * @var \DateTime
     */
    protected $now;

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
        return ['postUpdate','postRemove','postLoad','prePersist'];
    }

    /**
     * prePersist event listener
     *
     * @param  LifecycleEventArgs $args
     */
    /*
    interesting fact:  it appears that prePersist is NOT called on InterpreterEvent entities
    as result of updating the InterpreterEvents belonging to an Event entity
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $this->logger->debug('prePersist is happening on a '.get_class($entity));
        //exit("\nget the FUCK out!!!!!!!!\n");
        if ($entity instanceof Entity\InterpreterEvent) {
            $this->logger->debug("OK looks like prePersist with an InterpreterEvent entity");
            //$this->logger->debug(sprintf("auth is a what? %s",gettype($this->auth)));
            //$this->logger->debug(sprintf("eventEntity instance var is a what? %s",gettype($this->eventEntity)));
            $this->updateEventMetaData($args);
        }
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Entity\Event) {
            $this->state_before = [
                'modified' => $entity->getModified(),
                'modified_by' => $entity->getModifiedBy(),
            ];
            /*
            $interpreterEvents = $entity->getInterpreterEvents();
            foreach ($interpreterEvents as $ie) {
                $this->state_before['interpreter_ids'][] =
                    $ie->getInterpreter()->getId();
            }
            $this->state_before['modified'] = $entity->getModified();
            $this->logger->debug(sprintf(
                "postLoad: interp ids BEFORE: %s",
                print_r($this->state_before['interpreter_ids'],true)
            ));
            */
            $this->eventEntity = $entity;
            $this->logger->debug(__METHOD__.":  postLoad hanging onto \$eventEntity instance ");
            $this->logger->debug(__METHOD__.":  current user id is fuckin ".$this->auth->getIdentity()->id);
        }
    }



    /**
     * sets the AuthenticationService
     *
     * @param AuthenticationServiceInterface $auth
     * @return \InterpretersOffice\Entity\Listener\UpdateListener
     */
    public function setAuth(AuthenticationServiceInterface $auth)
    {
        $this->auth = $auth;

        return $this;
    }



    public function updateEventMetaData(LifecycleEventArgs $args)
    {
        $this->logger->debug("entering: ".__METHOD__);
        $previous_modified_by = $this->state_before['modified_by'];
        $current_user = $this->auth->getIdentity();
        if ($previous_modified_by->getUsername() != $current_user->username) {
            $user = $args->getObjectManager()->find(Entity\User::class,$current_user->id);
            $this->eventEntity->setModifiedBy($user);
            $this->logger->debug("we updated the modified_by property on shit, did we NOT????");
        }
        $this->eventEntity->setModified($this->getTimeStamp());
        $this->logger->debug("we definitely updated the modified property on shit, yes???");
    }


    /**
     * postUpdate handler
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function postUpdate(LifecycleEventArgs $args)
    {

        $entity = $args->getObject();
        $class = get_class($entity);
        $this->logger->debug(sprintf(
            '%s happening on entity %s',
            __METHOD__, $class));

        /** @var $cache Doctrine\Common\Cache\CacheProvider */
        $cache = $args->getObjectManager()->getConfiguration()
            ->getResultCacheImpl();
        switch ($class) {
            case Entity\Event::class:
                // flush everything, because there are so many related entities
                $success = $cache->flushAll();
                $this->logger->debug(
                    sprintf("ran flushAll() on %s in %s with result: %s",
                        $class, __METHOD__,$success ? "success":"failed"
                    )
                );
                break;
            case Entity\InterpreterEvent::class:
                $this->logger->debug("HOLY FUCKING SHIT it's an InterpreterEvent in postUpdate listener???");
                $this->updateEventMetaData($args);
                break;
            case Entity\User::class:
                // delete the cache entry
                $cache->setNamespace('users');
                $id = $entity->getId();
                if ($cache->contains($id)) {
                    $cache->delete($id);
                    $debug = sprintf("%s in UpdateListener deleted user id $id from cache", __FUNCTION__);
                } else {
                    $debug = sprintf('%s in UpdateListener: looks like users-cache has no item %d', __FUNCTION__, $id);
                }
                $this->logger->debug($debug);
                break;
            case Entity\InterpreterLanguage::class:
                $cache->setNamespace('languages');
                $cache->deleteAll();
                $cache->setNamespace('interpreters');
                $cache->deleteAll();
                $this->logger->debug("InterpreterLanguage entity updated; interpreters and language caches were purged.");
                break;
            default:
                $repository = $args->getObjectManager()->getRepository($class);
                // if $repository can delete its cache namespace, do it
                if ($repository instanceof CacheDeletionInterface) {
                    $repository->deleteCache();
                    $this->logger->debug("called delete cache on ".get_class($repository));
                } else {
                    $this->logger->debug("! not an implementation of CacheDeletionInterface: ".get_class($repository));
                }
                break;
        }

    }



    /**
     * postRemove event handler
     *
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Entity\InterpreterEvent) {
            $this->updateEventMetaData($args);
        }
        $this->logger->debug(sprintf(
            '%s happening on entity %s, bitch!',
            __METHOD__,
            get_class($entity)
        ));
        //return $this->postUpdate($args);
    }


}
