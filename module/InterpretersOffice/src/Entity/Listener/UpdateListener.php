<?php
/**
 * module/InterpretersOffice/src/Entity/Listener/UpdateListener.php
 */
namespace InterpretersOffice\Entity\Listener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;
use InterpretersOffice\Entity\Repository\CacheDeletionInterface;
use InterpretersOffice\Entity\InterpreterEvent;
use InterpretersOffice\Entity\EventType;
use InterpretersOffice\Entity;

use Zend\Log;


/**
 * entity listener for clearing caches
 *
 * Interesting facts:  if you delete an InterpreterEvent without adding any,
 * the postRemove event is triggered; if you add an InterpreterEvent without
 * removing any, the prePersist event is triggered; if you REPLACE, i.e.,
 * both and remove InterpreterEvent entities, then the postUpdate event is
 * triggered -- but neither prePersist nor postRemove
 *
 */
class UpdateListener implements EventSubscriber, Log\LoggerAwareInterface

{

    use Log\LoggerAwareTrait;
    //use CurrentUserTrait;


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

    protected $auth;

    public function setAuth($auth)
    {
        $this->auth = $auth;
        return $this;
    }

    protected $user;

    public function getAuthenticatedUser($args)
    {
        if (! $this->user) {
            $em = $args->getObjectManager();
            $id = $this->auth->getIdentity()->id;
            $this->user = $em->createQuery('select u FROM InterpretersOffice\Entity\User u WHERE u.id = :id')
                ->setParameters(['id'=>$id])->getOneOrNullResult();
        }
        return $this->user;
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
        return ['postUpdate','postRemove','postPersist','prePersist'];
    }

    /**
     * postUpdate listener
     *
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        return $this->clearCache($args,__FUNCTION__);
    }
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (get_class($entity) == Entity\InterpreterEvent::class) {
            $this->logger->debug("guess what: ievent being created, updating event");
            $entity->getEvent()->setModified($this->now);
            //$something = $args->getTarget();
            //$this->logger->debug('btw: getTarget() returns: '.(is_object($something) ? get_class($something):gettype($something)));
            $user = $this->getAuthenticatedUser($args);
            $object->setCreatedBy($user)->setCreated($this->now);
            $this->logger->debug("we set createdBy on InterpreterEvent here in ".__METHOD__);
        }
    }
    /**
     * clears cache
     *
     * @todo think about what happens when you ~update~ anything likely to have
     * related entities, e.g, language, event-type. we need to flush just about
     * everything, n'est-ce pas?
     *
     * @param LifecycleEventArgs $args
     * @param string $trigger name of function that called us
     * @return void
     */
    public function clearCache(LifecycleEventArgs $args, $trigger = null)
    {
        $entity = $args->getObject();
        $class = get_class($entity);
        $this->logger->debug(sprintf('%s happening on entity %s triggered by %s',
            __METHOD__, $class, $trigger ?: '(unknown)'));

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
                if ('postUpdate' == $trigger) {
                    $cache->flushAll(); // because.... why?
                }
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
                    $this->logger->debug(
                        "$class repository is not an implementation of CacheDeletionInterface: "
                        .get_class($repository));
                }
                break;
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
        return $this->clearCache($args,__FUNCTION__);
    }


    /**
     * postRemove event handler
     *
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        return $this->clearCache($args,__FUNCTION__);
    }

}
