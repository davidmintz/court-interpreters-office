<?php
/** module/Admin/src/Service/ScheduleListener.php  */

namespace InterpretersOffice\Admin\Service;

use Zend\EventManager\Event;
use Zend\Log\LoggerInterface;
use Zend\Authentication\AuthenticationServiceInterface;
use InterpretersOffice\Entity;
use InterpretersOffice\Requests\Entity\Request;
use InterpretersOffice\Admin\Service\ScheduleListener;
use InterpretersOffice\Requests\Entity\Listener\RequestEntityListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use Zend\Filter\Word\DashToCamelCase;

/**
 * listener for schedule changes
 */
class ScheduleListener
{

    /**
     * @var string
     */
    const CHANGE_DATE = 'change-date';

    /**
     * @var string
     */
    const CHANGE_TIME_X_PM = 'change-x-am-pm';

    /**
     * @var string
     */
    const CHANGE_TIME_WITHIN_AM_PM = 'change-within-am-pm';

    /**
     * @var string
     */
    const CHANGE_LANGUAGE = 'change-language';

    /**
     * LoggerInterface
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * auth
     * @var AuthenticationServiceInterface
     */
    private $auth;

    /**
     * configuration
     *
     * @var Array
     */
    protected $config;

    /**
     * constructor
     *
     * @param LoggerInterface                $log
     * @param AuthenticationServiceInterface $auth
     * @param Array                          $config
     */
    public function __construct(AuthenticationServiceInterface $auth,
        LoggerInterface $log, Array $config)
    {
        $this->logger = $log;
        $this->auth = $auth;
        $this->config = $config;
    }


    /**
     * sets auth instance
     *
     * primarily for rigging tests
     *
     * @param AuthenticationServiceInterface $auth
     * @return ScheduleListener;
     */
    public function setAuth(AuthenticationServiceInterface $auth)
    {
        $this->auth = $auth;

        return $this;
    }

    /**
     * scheduleChange observer
     *
     * @param  Event  $e
     * @return void
     */
    public function scheduleChange(Event $e)
    {
        $target = is_object($e->getTarget()) ? get_class($e->getTarget())
            : $e->getTarget();
        $this->logger->debug("ScheduleListener observing ".$e->getName()
            . " on target $target");
        if (Entity\Listener\EventEntityListener::class == $target) {
            return $this->eventUpdateHandler($e);
        }
        if (RequestEntityListener::class == $target) {
            $this->logger->debug(
                "ScheduleListener triggered by RequestEntityListener: "
                .$e->getName());

            $handler = 'on' . ucfirst($e->getName()) . 'Request';
            if (! method_exists($this, $handler)) {
                $this->logger->warn(sprintf(
                    '%s has no handler for event %s in %s',
                __CLASS__,$e->getName(),__FUNCTION__));

                return;
            }
            return $this->$handler($e);
        }
        $this->logger->info(sprintf(
            'hello, ScheduleListener not doing anything with %s: %s',
            $target, $e->getName()
        ));
    }

    /**
     * event listener for Request update
     *
     * @param  Event  $e
     * @return void
     */
    protected function onUpdateRequest(Event $e)
    {
        $this->logger->debug(
            sprintf('handling request update in %s at %d',__METHOD__,__LINE__)
        );
        /**
         * @var \InterpretersOffice\Requests\Entity\Request $request
         */
        $request = $e->getParam('entity');
        // is it on the schedule?
        /**
         * @var Entity\Event $scheduled_event
         */
        $scheduled_event = $request->getEvent();

        $this->logger->debug(
            __METHOD__.':  on the schedule? '.($scheduled_event ? 'yes':'no')
        );
        if (! $scheduled_event) {
            return;
        }
        $listener_config = $this->config['event_listeners'];
        /**
        * @var PreUpdateEventArgs $args
        */
        $args = $e->getParam('args');

        $user_action = $this->getUserActionName($args);
        $this->logger->debug("user action:  $user_action");

        $type = (string)$request->getEventType()->getCategory()
            == 'in' ? 'in-court':'out-of-court';
        $language = (string) $scheduled_event->getLanguage() == 'Spanish' ?
             'spanish':'non-spanish';
        $pattern = "/^(all-events|$type)\.(all-languages|$language)\./";

        // figure out what admin actions are configured for $user_action
        $actions = preg_grep($pattern,array_keys($listener_config[$user_action]));
        $filter = new DashToCamelCase();
        // and whether they are enabled
        foreach ($actions as $string) {
            $i = strrpos($string,'.') + 1;
            $action = substr($string,$i);
            $method = lcfirst($filter->filter($action));
            if ($listener_config[$user_action][$string]) {
                $this->logger->debug("need to call: $method()");
                if (method_exists($this, $method)) {
                    $this->$method($e,$action);
                } else {
                    $this->logger->warn("not running not-implemented $method");
                }
            } else {
                $this->logger->debug("not running disabled $method()");
            }
        }
    }
    /**
     * updates a scheduled Event to keep consistent with Request
     *
     * @param  Zend\EventManager\Event $event
     * @param string $user_action
     * @return ScheduleListener
     */
    public function updateScheduledEvent(Event $e, $user_action)
    {
        $changed_fields = $e->getParam('args')->getEntityChangeSet();
        $request = $e->getParam('entity');
        $event = $request->getEvent();
        $defendants_were_modified = $e->getParam('defendants_were_modified');
        if (! is_bool($defendants_were_modified)) {
            throw new \RuntimeException(sprintf(
                '%s is missing required "defendants_were_modified" Event parameter',
                __METHOD__));
        }
        $this->logger->debug("we are in ".__FUNCTION__. " to update Event from Request");

        return $this;
    }

    /**
     * event listener for Request creation
     *
     * @param  Event  $e
     * @return void
     */
    protected function onCreateRequest(Event $e)
    {
        $this->logger->debug(
            sprintf('handling request create in %s at %d',__METHOD__,__LINE__)
        );
        $listener_config = $this->config['event_listeners'];
    }
    protected function onCancelRequest(Event $e)
    {
        $this->logger->debug(
            sprintf('handling request cancel in %s at %d',__METHOD__,__LINE__)
        );
    }


    /**
     * eventUpdateHandler
     *
     * observes update, delete, etc on Event entities. Still a WIP.
     *
     * @param  Event  $e
     * @return void
     */
    public function eventUpdateHandler(Event $e)
    {
        $user = $this->auth->getIdentity()->username;
        $this->logger->debug(
            sprintf(
                'ScheduleListener: running %s with %s',
                __FUNCTION__,  $e->getName()
            )
        );
        switch ($e->getName()) {
            // case 'preRemove':
            //     break;
            case 'postRemove':
                $repo = $e->getParam('args')->getEntityManager()
                ->getRepository(Entity\Event::class);
                $entity = $e->getParam('entity');
                $data = $repo->getView($entity->getId());
                $info = [
                'user' => $user,
                'action' => $e->getName(),
                'event_data' => $data,
                ];
                $this->logger->info(json_encode($info));
                break;

            case 'postLoad':
                $this->logger->info("ScheduleListener: $user has loaded event id "
                . $e->getParam('entity')->getId());
                break;

            default:
                $this->logger->info(sprintf(
                    'ScheduleListener: user %s is doing %s with event id %d',
                    $user,
                    $e->getName(),
                    $e->getParam('entity')->getId()
                ));
        }
    }

    /**
     * Determines which user-action on a Request is to be handled.
     *
     * When users update a request, user-configured event listeners are
     * triggered. They may update more than one field. We look at the changeSet
     * to determine which update is the most signicant, and return the
     * corresponding action name, which is mapped to a method.
     *
     * @param  PreUpdateEventArgs $args
     * @return string name of the user action
     */
    private function getUserActionName(PreUpdateEventArgs $args)
    {
        if ($args->hasChangedField('language')) {
            return self::CHANGE_LANGUAGE;
        }
        if ($args->hasChangedField('date')) {
            // really?
            $old_value = $args->getOldValue('date')->format('Y-m-d');
            $new_value = $args->getNewValue('date')->format('Y-m-d');
            if ($old_value != $new_value) {
                return self::CHANGE_DATE;
            }
        }
        if ($args->hasChangedField('time')) {
            $old_value = $args->getOldValue('time')->format('H');
            $new_value = $args->getNewValue('time')->format('H');
            if ($old_value < 13 && $new_value >= 13
                or
                $old_value >= 13 && $new_value < 13
            ) {
                return self::CHANGE_TIME_X_PM;
            } else {
                return self::CHANGE_TIME_WITHIN_AM_PM;
            }

        }
    }
}
