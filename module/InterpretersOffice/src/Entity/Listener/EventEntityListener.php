<?php
/** module/InterpretersOffice/src/Entity/Listener/EventEntityListener.php */
namespace InterpretersOffice\Entity\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;

use Laminas\Log\LoggerAwareInterface;

use InterpretersOffice\Entity;
use Laminas\Log;
use Laminas\Authentication\AuthenticationServiceInterface;
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
        //$log->debug("postLoad callback in Event entity listener: triggering postLoad");
        $this->previous_defendants = $entity->getDefendants()->toArray();
        $this->previous_interpreters = $entity->getInterpreterEvents()->toArray();
        $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            [   'entity' => $entity,
                'args' => $args,
            ]
        );
    }

    /**
     * postRemove callback
     *
     * @param Entity\Event $entity
     * @param LifecycleEventArgs $args
     */
    public function postRemove(
        Entity\Event $entity,
        LifecycleEventArgs $args
    ) {
        $auth_user = $this->getAuthenticatedUser($args);
        $user = $auth_user ? $auth_user->getUsername() : '<nobody>';
        $message = "user $user deleted (purged) event id {$entity->getId()}";
        $this->logger->info($message, [
            'entity_class' => Entity\Event::class,
            'entity_id' => $entity->getId(),
            'description' => $entity->describe(),
        ]);
    }

    /**
     * was data really updated?
     *
     * Doctrine thinks the entity changed even when the values of datetime
     * fields have not been modified. So we compare the before and after states
     * for equivalence. We also check the defendant-names collection for
     * modification.
     *
     * @param  Entity\Event $entity
     * @param  PreUpdateEventArgs $args
     * @return array
     */
    private function reallyModified(
        Entity\Event $entity,
        PreUpdateEventArgs $args
    ) : Array {

        $fields_updated = array_keys($args->getEntityChangeSet());
        $this->logger->debug(__METHOD__.": looks like updates to:\n"
            . implode('; ', $fields_updated));

        $interpreterEvents = $entity->getInterpreterEvents()->toArray();
        if ($interpreterEvents != $this->previous_interpreters) {
            $fields_updated[] = 'interpreters';
            $this->logger->debug("interpreters were modified, fool!");
            $removed = array_diff($this->previous_interpreters, $interpreterEvents);
            if (count($removed)) {
                $who = implode(", ", array_map(function ($ie) {
                    return $ie->getInterpreter()->getFullName();
                }, $removed));
                $what = $entity->describe();
                $user = $this->getAuthenticatedUser($args);
                // the authenticated user might not be doing it directly...
                $user_role = (string)$user->getRole();
                if ($user_role != 'submitter') {
                    $message = sprintf(
                        "user %s removed %s from event #%d (%s)",
                        $user->getUsername(),
                        $who,
                        $entity->getId(),
                        $what
                    );
                    $this->logger->info($message, [
                        'entity_class' => get_class($entity),
                        'entity_id' => $entity->getId(),
                        'channel'  => 'scheduling',
                    ]);
                }
                // else {
                //     $message = sprintf(
                //         "%s has been removed from event #%d (%s); current user is %s",
                //         $who, $entity->getId(),$what,$user->getUsername()
                //     );
                // }
            }
        }

        $defendants = $entity->getDefendants()->toArray();

        if ($defendants != $this->previous_defendants) {
            $this->logger->debug("defendants were modified");
            $fields_updated[] = 'defendants';
        }

        return $fields_updated;
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

        $fields_updated = $this->reallyModified($entity, $args);
        $user = $this->getAuthenticatedUser($args);
        //$shit = array_keys($args->getEntityChangeSet());
        if (in_array('deleted', $fields_updated) && $entity->getDeleted()) {
            $id = $entity->getId();
            if ((string)$user->getRole() !== 'submitter') {
                $message = sprintf(
                    'user %s deleted event #%d from the schedule',
                    $user->getUsername(),
                    $id
                );
                $this->logger->info(
                    $message,
                    ['entity_class' => Entity\Event::class,'entity_id' => $id,'channel' => 'scheduling', ]
                );
            }
        }
        if ($fields_updated) {
            $entity->setModified($this->now);
            $entity->setModifiedBy($user);
            if (count($entity->getInterpreters())) {
                $changeset = $args->getEntityChangeSet();
                $this->logger->debug("what is being changed? ",[array_keys($changeset)]);
                if (in_array('cancellation_reason',array_keys($changeset))) {
                    // if it's going from null to not null or vice-versa, that's significant

                }
                foreach (['date','time','cancellation_reason'] as $something_important) {


                }
            }
            
        }
        /* 
            now the interesting part: guess the criteria for resetting the 
            sent_confirmation_email property of the related InterpreterEvents;
        */
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
        $this->logger->debug(__METHOD__. ": set metadata");
    }
}
