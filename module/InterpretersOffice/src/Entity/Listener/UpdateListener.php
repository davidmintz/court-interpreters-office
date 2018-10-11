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
     * auth service
     *
     * @var AuthenticationServiceInterface;
     */
    protected $auth;

    /**
     * names of classes and triggering events
     *
     * @var Array
     */
    private $caches_to_clear = [];

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
        return ['postUpdate','postRemove','postPersist',
            'prePersist','postFlush',];
    }


    /**
     * postUpdate listener
     *
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $this->entity = $entity;
        /** @todo this is bullshit. get rid of it */
        $this->caches_to_clear[] =
            ['class' => get_class($entity),'trigger' => __FUNCTION__];
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
            $this->logger->debug("interp_event is being created, updating event");
            $entity->getEvent()->setModified($this->getTimeStamp());
            $user = $this->getAuthenticatedUser($args);
            $entity->setCreatedBy($user)->setCreated($this->getTimeStamp());
            $this->logger->debug("we set createdBy on InterpreterEvent here in ".__METHOD__);
        } elseif ($entity instanceof Request) {
            $now = $this->getTimeStamp();
            $user = $this->getAuthenticatedUser($args);
            $person = $this->getCurrentUserPerson($args);
            $entity->setCreated($now)
                ->setModified($now)
                ->setSubmitter($person)
                ->setModifiedBy($user);
        }
    }

    /**
     * postFlush event listener
     *
     * After flush, we clear the cache as needed. The events that might require
     * a cache-clearing are queued up in $this->caches_to_clear. We loop through
     * and if we encounter a reasons to clear the whole cache, we exit the loop
     * because there is nothing more to do. In certain cases (updating
     * DefendantEvent collections) we might otherwise pointlessly clear the
     * cache several times.
     *
     * @param  PostFlushEventArgs $args
     * @return void
     */
    public function postFlush(PostFlushEventArgs $args)
    {

        if (! $this->caches_to_clear) {
            $this->logger->info("postFlush() thinks no cache needs clearing.");
            return;
        }
        //$this->logger->debug('$this->caches_to_clear: '.print_r($this->caches_to_clear,true));
        if (count($this->caches_to_clear) > 1) {
            // clear out duplicates
            $tmp  = array_unique(array_map(function ($i) {
                return json_encode($i);
            }, $this->caches_to_clear));
            $this->caches_to_clear = array_map(function ($i) {
                return json_decode($i, \JSON_OBJECT_AS_ARRAY);
            }, $tmp);
        }
        //$this->logger->debug('and now: '.print_r($this->caches_to_clear,true));
        $em = $args->getEntityManager();
        /** @var $cache Doctrine\Common\Cache\CacheProvider */
        $cache = $args->getEntityManager()->getConfiguration()
            ->getResultCacheImpl();


        foreach ($this->caches_to_clear as $event) {
            switch ($event['class']) {
                case Entity\Event::class:
                case 'DoctrineORMModule\Proxy\__CG__\InterpretersOffice\Entity\Event':
                    // flush everything, because there are so many related entities
                    $success = $cache->flushAll();
                    $this->logger->debug(
                        sprintf(
                            "ran flushAll() on %s in %s with result: %s, done.",
                            $event['class'],
                            __METHOD__,
                            $success ? "success" : "failed"
                        )
                    );
                    break 2;

                case Entity\User::class:
                    // delete the cache entry
                    /** @todo  this is fucked up and needs re-thinking **/
                    if ('postUpdate' == $event['trigger']) {
                        $entity = $this->entity;
                        $cache->setNamespace('users');
                        $id = $entity->getId();
                        if ($cache->contains($id)) {
                            $cache->delete($id);
                            $debug = sprintf("%s in UpdateListener deleted user id $id from cache", __FUNCTION__);
                        } else {
                            $debug = sprintf('%s in UpdateListener: looks like users-cache has no item %d', __FUNCTION__, $id);
                        }
                        $this->logger->debug($debug);
                    } else {
                        $success = $cache->flushAll();
                        $debug = sprintf(
                            "ran flushAll() on %s in %s with result: %s",
                            $event['class'],
                            __METHOD__,
                            $success ? "success" : "failed"
                        );
                        $this->logger->debug($debug);
                    }

                    break 2;

                case Entity\InterpreterLanguage::class:
                    $cache->setNamespace('languages');
                    $cache->deleteAll();
                    $cache->setNamespace('interpreters');
                    $cache->deleteAll();
                    $this->logger->debug("InterpreterLanguage entity updated; interpreters and language caches were purged.");
                    break 2;

                default:
                    $repository = $args->getEntityManager()->getRepository($event['class']);
                    // if $repository can delete its cache namespace, do it
                    if ($repository instanceof CacheDeletionInterface) {
                        $repository->deleteCache();
                        $this->logger->debug("called delete cache on ".get_class($repository));
                    } else {
                        $this->logger->debug(
                            "$event[class] repository doesn't implement CacheDeletionInterface: "
                            .get_class($repository)
                        );
                    }
            }
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
        $this->caches_to_clear[] = ['class' => get_class($entity),'trigger' => __FUNCTION__];
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
        $this->caches_to_clear[] = ['class' => get_class($entity),'trigger' => __FUNCTION__];
    }
}
