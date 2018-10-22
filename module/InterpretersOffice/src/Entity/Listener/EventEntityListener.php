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

    /**
     * array of defendant names for later comparison
     *
     * @var Array
     */
    protected $previous_defendants;

    /**
     * array of InterpreterEvent for later comparison
     *
     * @var Array
     */
    protected $previous_interpreters;

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
        $log = $this->getLogger();
        $log->debug("postload callback running in Event entity listener");
        $this->previous_defendants = $entity->getDefendants()->toArray();
        $this->previous_interpreters = $entity->getInterpreterEvents()->toArray();
        //  temporary/debugging
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
        Entity\Event $entity,
        LifecycleEventArgs $args
    ) {
        $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            compact('args', 'entity')
        );
    }
    /**
     * was data really updated?
     *
     * Doctrine thinks the entity changed even when the values of datetime
     * fields have not been modified. So we compare the before and after states
     * for equivalence. We also check the defendant-names collection for
     * modification.
     *
     * @param  Entity\Event] $entity
     * @param  PreUpdateEventArgs $args
     * @return boolean
     */
    private function reallyModified(Entity\Event $entity,
        PreUpdateEventArgs $args)
    {

        $interpreterEvents = $entity->getInterpreterEvents()->toArray();
        if ($interpreterEvents != $this->previous_interpreters) {
            $this->logger->debug("interpreters were updated");
            return true;
        }
        $fields_updated = array_keys($args->getEntityChangeSet());
        $datetimes = ['date','time','submission_date','submission_time','end_time'];
        if (array_diff($fields_updated,$datetimes)) {
            $this->logger
                ->debug("fields other than dates and times were modified");
            return true;
        }
        foreach (['time','submission_time','end_time'] as $time) {
            $before = $args->getOldValue($time)->format('H:i');
            $after = $args->getNewValue($time)->format('H:i');
            if ($before != $after) {
                $this->logger->debug("event $time was modified");
                return true;
            }
        }
        foreach (['date','submission_date'] as $date) {
            if ($args->getOldValue($date) !=
                $args->getNewValue($date)) {
                $this->logger->debug("event $date was modified");
                return true;
            }
        }
        $defendants = $entity->getDefendants()->toArray();

        if ($defendants != $this->previous_defendants) {
            $this->logger->debug("defendants were modified");
            return true;
        }
        $this->logger->debug("NOTHING really modified in Event entity");
        return false;
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

        if ($this->reallyModified($entity,$args)) {
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
     * @param \InterpretersOffice\Entity\Event $entity
     * @param LifecycleEventArgs $args
     */
    public function prePersist(Entity\Event $entity, LifecycleEventArgs $args)
    {
        if (! $entity->getCreatedBy()) {
            // because in test environment, this might already have been done
            // for us
            $user = $this->getAuthenticatedUser($args);
            $entity->setCreatedBy($user);
        } else {
            // so we don't blow up in the test environment
            $user = $entity->getCreatedBy();
        }
        $entity->setCreated($this->now)
                ->setModifiedBy($user)
                ->setModified($this->now);
        $this->logger->debug(__FUNCTION__
        . " in EventEntityListener really did shit");
        $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            compact('args', 'entity')
        );
    }
}

/*
$really_modified = false;
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
    $really_modified = true;
}
 */
