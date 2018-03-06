<?php
/**
 * module/InterpretersOffice/src/Entity/Listener/UpdateListener.php
 */
namespace InterpretersOffice\Entity\Listener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use InterpretersOffice\Entity\Repository\CacheDeletionInterface;
use InterpretersOffice\Entity\Event;

use InterpretersOffice\Entity;

use Zend\Log;
use Zend\Authentication\AuthenticationServiceInterface;

/**
 * entity listener for clearing cache
 */
class UpdateListener implements EventSubscriber, Log\LoggerAwareInterface
{

    use Log\LoggerAwareTrait;

    /**
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
    protected $state_before;

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
        return ['postUpdate','postPersist','postRemove','postLoad'];//'postLoad'
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Entity\Event) {
            $this->state_before = [ 'interpreter_ids' => []];
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
            $this->eventEntity = $entity;
        }
    }

    /*
     * postLoad handler
     *
     * keeps a reference to the Event entity in order to update
     * its meta later on in listeners that observe related entities
     *
     * @param LifecycleEventArgs $args

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Entity\Event) {
            $this->eventEntity = $entity;
        }
    }
    */

    /**
     * sets the AuthenticationService
     *
     * @param AuthenticationServiceInterface $auth
     * @return \InterpretersOffice\Entity\Listener\UpdateListener
     */
    public function setAuthenticationService(AuthenticationServiceInterface $auth)
    {
        $this->auth = $auth;

        return $this;
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
                $this->logger->debug("InterpreterLanguage entity updated; language cache was purged.");
                break;
            default:
                # code...
                break;
        }
        $repository = $args->getObjectManager()->getRepository($class);
        // if $repository can delete its cache namespace, do it
        if ($repository instanceof CacheDeletionInterface) {
            $repository->deleteCache();
            $this->logger->debug("called delete cache on ".get_class($repository));
        } else {
            $this->logger->debug("! not an implementation of CacheDeletionInterface: ".get_class($repository));
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
        $entity = $args->getObject();
        if ($entity instanceof Entity\InterpreterEvent) {
            $this->logger->debug("HELLO MOTHERFUCKER! this is an InterpreterEvent being created");
            if ($this->eventEntity) {
                $this->logger->debug("and we have an Event stashed away, whose timestamp and lastmodified_by should now be UPDATED");
            }
        }
        $this->logger->debug(sprintf(
            '%s happening on entity %s, bitch!',
            __METHOD__,
            get_class($entity)
        ));
        //return $this->postUpdate($args);
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
        $this->logger->debug(sprintf(
            '%s happening on entity %s, bitch!',
            __METHOD__,
            get_class($entity)
        ));
        //return $this->postUpdate($args);
    }


}
