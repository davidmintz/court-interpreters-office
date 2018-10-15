<?php
/** module/InterpretersOffice/src/Entity/Listener/EventEntityListener.php */
namespace InterpretersOffice\Entity\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;

use Zend\Log\LoggerAwareInterface;

use InterpretersOffice\Entity;
use Zend\Log;
use Zend\Authentication\AuthenticationServiceInterface;
use InterpretersOffice\Service\Authentication\CurrentUserTrait;
use Doctrine\ORM\EntityManager;

/**
 * Event entity listener.
 * Responsible for making sure certain meta data elements are set correctly.
 * For cache-related functions see {@see UpdateListener}
 */
class EventEntityListener implements EventManagerAwareInterface, LoggerAwareInterface
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
     * constructor
     *
     * @param \DateTime
     */
    public function __construct()
    {
        $this->now = new \DateTime();
    }

    protected $before = [

    ];

    /**
     * sets authentication service
     *
     * @param AuthenticationServiceInterface $auth
     * @return EventEntityListener
     */
    public function setAuth(AuthenticationServiceInterface $auth)
    {
        $this->auth = $auth;

        return $this;
    }
    /**
     * postLoad callback
     *
     * @param Entity\Event $entity
     * @param LifecycleEventArgs $args
     */
    public function postLoad(
        Entity\Event $entity,
        LifecycleEventArgs $args
    ) {
        $this->before = [
            'date'=>$entity->getDate(),
            'time' =>$entity->getTime(),
            'submission_date' => $entity->getSubmissionDate(),
            'submission_time' => $entity->getSubmissionTime(),
        ];
        // just a temporary/debugging thing
        $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            compact('args', 'entity')
        );
    }
    /**
     * preRemove callback
     *
     * @param Entity\Event $eventEntity
     * @param LifecycleEventArgs $args
     */
    public function preRemove(
        Entity\Event $eventEntity,
        LifecycleEventArgs $args
    ) {
        $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            compact('args', 'eventEntity')
        );
    }

    /**
     * preUpdate callback
     *
     * @param Entity\Event $entity
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(
        Entity\Event $entity,
        PreUpdateEventArgs $args
    ) {

        $truly_modified = false;
        $changeset = $args->getEntityChangeSet();
        $after = [
            'date'=>$entity->getDate(),
            'time' =>$entity->getTime(),
            'submission_date' => $entity->getSubmissionDate(),
            'submission_time' => $entity->getSubmissionTime(),
        ];

        foreach ($after as $field => $object) {
            if (strstr($field,'time')) {
                // compare only the time part of the objects
                $previous = $object->format("H:i");
                $current  = $this->before[$field]->format("H:i");
                if ($previous == $current) {
                    unset($changeset[$field]);
                }
            } else {
                //compare objects, as in equivalence
                if ($this->before[$field] == $object) {
                    unset($changeset[$field]);
                }
            }
        }
        if (count($changeset)) {
            $truly_modified = true;
        }
        if ($truly_modified && ! $args->hasChangedField('modified')) {
            $entity->setModified($this->now);
            $entity->setModifiedBy($this->getAuthenticatedUser($args));
        }

        $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            compact('args', 'entity')
        );
    }

    /**
     * prePersist callback
     *
     * sets Event metadata, e.g., who created the Event and when
     *
     * @param \InterpretersOffice\Entity\Event $eventEntity
     * @param LifecycleEventArgs $args
     */
    public function prePersist(Entity\Event $eventEntity, LifecycleEventArgs $args)
    {
        if (! $eventEntity->getCreatedBy()) {
            // because in test environment, this might already have been done
            // for us
            $user = $this->getAuthenticatedUser($args);
            $eventEntity->setCreatedBy($user);
        } else {
            // so we don't blow up in the test environment
            $user = $eventEntity->getCreatedBy();
        }
        $eventEntity->setCreated($this->now)
                ->setModifiedBy($user)
                ->setModified($this->now);
        $this->logger->debug(__FUNCTION__
        . " in EventEntityListener really did shit");
        $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            compact('args', 'eventEntity')
        );
    }
}
