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
     * whether our modification metadata has been changed
     *
     * The name is a little misleading in that are also thinking of
     * our modified_by (User) property.
     *
     * @var boolean
     */
    private $timestamp_was_updated = false;

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
     * @param Entity\Event $eventEntity
     * @param LifecycleEventArgs $event
     */
    public function postLoad(
        Entity\Event $eventEntity,
        LifecycleEventArgs $event
    ) {
        //$this->state_before['defendants'] = $eventEntity->getDefendantNames()
        //    ->toArray();
        $this->getEventManager()->trigger(__FUNCTION__, $this);
    }

    /**
     * preUpdate callback
     *
     * @param Entity\Event $eventEntity
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(Entity\Event $eventEntity,
        PreUpdateEventArgs $args)
    {

        if ($this->timestamp_was_updated) {
            $this->logger->debug(__METHOD__.
            ": we think last-modification update has been done, returning");
            return;
        }
        $this->logger->debug(sprintf(
            'event modification detected, setting modified and modifiedBy on '
            . ' event entity in %s line %d', __METHOD__,__LINE__
        ));
        if (! $args->hasChangedField('modified')) {
            $eventEntity->setModified($this->now);
        }
        $this->logger->debug("for the record, I am EventEntityListener: ".spl_object_hash($this));
        $eventEntity->setModifiedBy($this->getAuthenticatedUser($args));
        $this->timestamp_was_updated = true;
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
        . " in EventEntityListener prePersist really did shit");
    }


}
