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
use InterpretersOffice\Entity\InterpreterEvent;
use InterpretersOffice\Entity;
use InterpretersOffice\Requests\Entity\Request;
//use InterpretersOffice\Module;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Log;
use InterpretersOffice\Service\Authentication\CurrentUserTrait;

use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;

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
class UpdateListener implements
    EventSubscriber,
    Log\LoggerAwareInterface,
    EventManagerAwareInterface
{

    use Log\LoggerAwareTrait;
    use CurrentUserTrait;
    use EventManagerAwareTrait;

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
        return ['postUpdate','preRemove','postPersist','prePersist',];
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
        $entity_class = get_class($entity);
        if (method_exists($entity, 'getId')) {
            $entity_id = $entity->getId();
        } else {
            $entity_id = null;
        }
        $auth_user = $this->getAuthenticatedUser($args);
        $user = $auth_user ? $auth_user->getUsername() : '<nobody>';
        $channel = '';
        switch ($entity_class) {
            case Entity\Event::class:
                $message = "user $user added a new event: ".$entity->describe();
                $channel = 'scheduling';
                break;
            case Entity\InterpreterEvent::class:
                $who = $entity->getInterpreter()->getFullName();
                $what = $entity->getEvent()->describe();
                $message = "user $user assigned $who to $what";
                $entity_id = $entity->getEvent()->getId();
                $entity_class = Entity\Event::class;
                $channel = 'scheduling';
                break;
            case 'InterpretersOffice\Admin\Rotation\Entity\RotationMember':
            case 'InterpretersOffice\Admin\Rotation\Entity\Rotation':
            case 'InterpretersOffice\Admin\Rotation\Entity\Task':
                $channel = 'rotations';
                $what = strtolower(substr($entity_class, strrpos($entity_class, '\\') + 1));
                $message = "user $user created a new $what";
                break;
            default:
                $basename = strtolower(substr($entity_class, strrpos($entity_class, '\\') + 1));
                $channel = "{$basename}s";
                $message = sprintf('user %s added a new %s', $user, $basename);
                if (method_exists($entity, '__toString')) {
                    $message .= ": $entity";
                } elseif (method_exists($entity, 'getFullName')) {
                    $message .= ": {$entity->getFullName()}";
                }
        }

        $this->logger->info(
            $message, compact('entity_id', 'entity_class','channel')
        );
        $this->clearCache($args);
    }

    /**
    * postRemove event handler
    *
    * @param LifecycleEventArgs $args
    * @return void
    */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $entity_class = get_class($entity);
        $channel = null;
        if (in_array($entity_class, [Entity\InterpreterEvent::class, Entity\Event::class])) {
            return;
        }
        $basename = strtolower(substr($entity_class, strrpos($entity_class, '\\') + 1));
        if ($basename == 'person') {
            $channel = 'people';
        }
        $channel = $channel ?: "{$basename}s";
        $what = $basename;
        if (method_exists($entity,'__toString')) {
            $what .= " $entity";
        } elseif (method_exists($entity,'getFullName')) {
            $what .= " {$entity->getFullName()}";
        }
        $this->logger->info(
            sprintf(
                'user %s deleted an entity (%s)',
                $this->getAuthenticatedUser($args)->getUsername(),
                $what
            ),
            ['entity_class' => $entity_class, 'entity_id' =>
                method_exists($entity, 'getId') ? $entity->getId() : null,
                'channel'=>$channel,
            ]
        );
        $this->clearCache($args);
    }

    /**
     * clears cache if possible
     *
     * @param LifecycleEventArgs $args
     * @return void
     */
    private function clearCache(LifecycleEventArgs $args)
    {
        $class = get_class($args->getObject());
        $repository = $args->getEntityManager()->getRepository($class);
        if ($repository instanceof CacheDeletionInterface) {
            $repository->deleteCache();
            $this->logger->debug(
                sprintf(
                    'cleared cache on CacheDeletionInterface instance %s',
                    $class
                )
            );
        } else {
            $this->logger->debug(
                "$class repository does not implement CacheDeletionInterface, not clearing"
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
        // $basename = strtolower(substr($entity_class, strrpos($entity_class, '\\') + 1));
        // $channel = "{$basename}s";
        $this->logger->debug(
            sprintf(
                'updated entity %s, current user is %s ',
                get_class($args->getObject()),
                $user ? $user->getUsername() : 'nobody'
            )
        );
        $this->clearCache($args);
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
            $user = $this->getAuthenticatedUser($args);
            $entity->setCreatedBy($user)->setCreated($this->getTimeStamp());
            $this->logger->debug(
                "set created_by and timestamp on InterpreterEvent in ".__METHOD__
            );
        }
    }
}
