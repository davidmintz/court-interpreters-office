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

    /**
     * authentication service
     *
     * @var AuthenticationServiceInterface
     */
    protected $auth;

    /**
     * currently authenticated user
     *
     * @var Entity\User
     */
    protected $user;

    /**
     * holds a copy of related entities before updating
     *
     * @var array
     */
    protected $state_before = [
        'interpreterEvents' => [],
        'defendants'   => [],
    ];

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
        $this->state_before['defendants'] = $eventEntity->getDefendantNames()
            ->toArray();
        $this->state_before['interpreterEvents'] =
            $eventEntity->getInterpreterEvents()->toArray();
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
        $this->logger->debug(__METHOD__. " is running");
        $modified = false;
        $debug = '';
        if ($args->getEntityChangeSet()) {
            $modified = true;
            // really?
            $debug .= "what changed? "
                    .print_r(array_keys($args->getEntityChangeSet()), true);
        }

        $interpreters_before = $this->state_before['interpreterEvents'];
        $interpreters_after = $eventEntity->getInterpreterEvents()->toArray();

        if ($interpreters_before != $interpreters_after) {
             $modified = true;
             $added = array_diff($interpreters_after, $interpreters_before);
             // client-side-supplied created_by should agree with
             // currently authenticated user, but new entities can't get
             // inserted without a created_by id (we can't cascade=persist
             // for Interpreter with null id), so we have to set it in a hidden
             // form field, check it after the fact, and correct it if (in the
             // improbable case) it's necessary (until we come up with a better
             // plan)
             /** @todo factor out into its own function?  */
            $current_user_id = $this->auth->getIdentity()->id;
            foreach ($added as $ie) {
                $creator_id = $ie->getCreatedBy()->getId();
                if ($creator_id != $current_user_id) {
                    $interpreter = $ie->getInterpreter();
                    $this->logger->warn(
                        sprintf(
                            'submitted creator id inconsistent with current user'
                            . ' in event id %d, interpreter id %d (%s)',
                            $eventEntity->getId(),
                            $added, $interpreter->getLastname()
                        ), compact('creator_id', 'current_user_id')
                    );
                }
                $ie->setCreatedBy(
                    $this->getAuthenticatedUser($args->getEntityManager())
                );
            }
        }
        $defendants_before = $this->state_before['defendants'];
        $defendants_after = $eventEntity->getDefendantNames()->toArray();
        if ($defendants_after != $defendants_before) {
            $modified = true;
            $this->logger->debug("defendants YES modified, right?");
        }
        if ($modified) {
            $this->logger->debug(sprintf(
                'event modification detected, setting modified and modifiedBy on '
                . ' event entity in %s line %d', __METHOD__,__LINE__
            ));
            $eventEntity
                    ->setModified($this->now)
                    ->setModifiedBy(
                        $this->getAuthenticatedUser($args->getEntityManager())
                    );
            $debug .= sprintf(
                " real changes detected, we updated event meta for event id %s",
                $eventEntity->getId());
        } else {
            $debug .= "no actual update detected with event id "
                    .$eventEntity->getId();
        }
        $this->logger->info($debug);
    }


    /**
     * prePersist callback
     *
     * sets Event metadata, e.g., who created the Event and when
     *
     * @param \InterpretersOffice\Entity\Event $eventEntity
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Entity\Event $eventEntity, LifecycleEventArgs $event)
    {

        if (! $eventEntity->getCreatedBy()) {
            // because in test environment, this might already have been done
            // for us
            $user = $this->getAuthenticatedUser($event->getEntityManager());
            $eventEntity->setCreatedBy($user);
        } else {
            // so we don't blow up in the test environment
            $user = $eventEntity->getCreatedBy();
        }

        $eventEntity->setCreated($this->now)
                ->setModifiedBy($user)
                ->setModified($this->now);
        foreach ($eventEntity->getInterpreterEvents() as $interpreterEvent) {
            $interpreterEvent
                    ->setCreatedBy($user)
                    ->setCreated($this->now);
        }
        $this->logger->debug(__FUNCTION__ . " in EventEntityListener really did shit");
    }

    /**
     * lazy-gets the User entity corresponding to authenticated identity
     *
     * @param EntityManager $em
     * @return Entity\User
     */
    protected function getAuthenticatedUser(EntityManager $em)
    {
        if (! $this->user) {
            $dql = 'SELECT u FROM InterpretersOffice\Entity\User u WHERE u.id = :id';
            $id = $this->auth->getIdentity()->id;
            $query = $em->createQuery($dql)
                    ->setParameters(['id' => $id])
                    ->useResultCache(true);
            $this->user = $query->getOneOrNullResult();
        }

        return $this->user;
    }
}
