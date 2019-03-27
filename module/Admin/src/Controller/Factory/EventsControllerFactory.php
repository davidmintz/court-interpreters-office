<?php
/** module/InterpretersOffice/src/Controller/Factory/EventsControllerFactory.php */

namespace InterpretersOffice\Admin\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Authentication\AuthenticationServiceInterface;
use Interop\Container\ContainerInterface;
use InterpretersOffice\Admin\Controller\EventsController;

use InterpretersOffice\Entity\Listener;

/**
 * Factory for instantiating EventController
 */
class EventsControllerFactory implements FactoryInterface
{

    /**
     * instantiates and returns a concrete instance of AbstractActionController.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return Zend\Mvc\Controller\AbstractActionController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $auth = $container->get('auth');
        $em = $container->get('entity-manager');
        $controller = new EventsController(
            $em,
            $auth // maybe we won't need this
        );
        //attach the entity listeners
        $resolver = $em->getConfiguration()->getEntityListenerResolver();
        $resolver->register($container->get(Listener\EventEntityListener::class));
        $resolver->register($container->get(Listener\UpdateListener::class)->setAuth($auth));
        // experimental
        $sharedEvents = $container->get('SharedEventManager');
        $log = $container->get('log');
        /**
         * this next bit is a shit-show but never fear, we  will clean it up
         */
        $sharedEvents->attach(
            'InterpretersOffice\Entity\Listener\EventEntityListener',
            'postLoad',
            function($e) use ($log) {
                //return;
                $params = $e->getParams();
                $entity = $params['entity'];
                $id = $entity->getId();

                if (false) {
                    $args = $params['args'];
                    $em = $args->getObjectManager();
                    $view_before = $em->getRepository(get_class($entity))->getView($entity->getId());
                    $log->debug("using call to repository to get event snapshot");
                } else {
                    $log->debug("using in-memory entity to get event snapshot");
                    $view_before = [
                        'date'=>$entity->getDate(),
                        'time'=>$entity->getTime(),
                        'end_time'=>$entity->getEndTime(),
                        'last_updated'=>$entity->getModified(),
                        // this is a little aggressive...
                        'last_updated_by' => $entity->getModifiedBy()->getUserName(),
                        'judge' => $entity->getStringifiedJudgeOrWhatever(),
                        'type'  => (string)$entity->getEventType(),
                        'category' => (string)$entity->getEventType()->getCategory(),
                        'submission_date'=>$entity->getSubmissionDate(),
                        'submission_time'=>$entity->getSubmissionTime(),
                        'defendants' => array_map(function($d){
                            return ['surnames'=>$d->getSurnames(),'given_names'=>$d->getGivenNames()];
                        },$entity->getDefendants()->toArray()),
                        'language' => (string)$entity->getLanguage(),
                        'docket' => $entity->getDocket(),
                        'interpreters' => array_map(
                            function($ie){
                                $i = $ie->getInterpreter();
                                return [
                                    'lastname'=> $i->getLastname(),
                                    'firstname'=> $i->getFirstName(),
                                    'email'=>$i->getEmail(),
                                    'id'=>$i->getId()
                                ];
                            },$entity->getInterpreterEvents()->toArray()
                        ),
                        /**
                         * @todo this is nuts. write a method on the entity
                         * class that gets this string.
                         */
                        'location' => call_user_func(function($event){
                            $location = $event->getLocation();
                            $string = '';
                            if ($location) {
                                $string = (string)$location;
                                $parent = $location->getParentLocation();
                                if ($parent) {
                                    $string .= ", $parent";
                                }
                            }
                            return $string;
                        },$entity),
                        'parent_location'=>$entity->getLocation() ?
                            $entity->getLocation()->getParentLocation():'',
                        'aj_default_location'=> $entity->getAnonymousJudge()?
                            (string)$entity->getAnonymousJudge()->getDefaultLocation():'',
                        'default_courtroom' => call_user_func(
                            function($judge){
                                if (! $judge) { return ''; }
                                $location = $judge->getDefaultLocation();
                                return $location ? $location->getName():'';
                            },
                            $entity->getJudge()
                        ),
                        'default_courthouse' => call_user_func(
                            function($judge){
                                if (! $judge) { return ''; }
                                $location = $judge->getDefaultLocation();
                                if ($location && $location->getParentLocation()) {
                                    return $location->getParentLocation()->getName();
                                } else {
                                    return '';
                                }
                            },
                            $entity->getJudge()
                        ),
                        'submitter' => call_user_func(
                            function($event) {
                                $person = $event->getSubmitter();
                                if ($person) {
                                    $return = $person->getFirstName().' '.$person->getLastname();
                                    $return .= ', '.(string)$person->getHat();
                                    return $return;
                                } else {
                                    return (string)$event->getAnonymousSubmitter();
                                }
                            },$entity
                            ),
                        'submitter_hat'=> $entity->getSubmitter() ?
                            (string)$entity->getSubmitter()->getHat() : '',
                        'comments' => $entity->getComments(),
                        'admin_comments' => $entity->getAdminComments(),
                        'reason_for_cancellation' => $entity->getCancellationReason() ?
                            (string)$entity->getCancellationReason():'n/a',
                    ];
                }
                $session = new \Zend\Session\Container("event_updates");
                $session->$id = $view_before;
                $log->debug("stored entity state in session {$session->getName()}"
                     ." (id $id) for later reference");
                // */
                /* fields we need...
                 Array
                    (
                     [0] => id
                     [1] => date
                     [2] => time
                     [3] => end_time
                     [4] => judge
                     [5] => type
                     [6] => category
                     [7] => language
                     [8] => docket
                     [9] => location
                     [10] => parent_location
                     [11] => default_courtroom
                     [12] => default_courthouse
                     [13] => aj_default_location
                     [14] => submitter
                     [15] => submitter_hat
                     [16] => submission_date
                     [17] => submission_time
                     [18] => created_by
                     [19] => created
                     [20] => last_updated_by
                     [21] => last_updated
                     [22] => comments
                     [23] => admin_comments
                     [24] => reason_for_cancellation
                     [25] => request_id
                     [26] => submitter_comments
                     [27] => defendants
                     [28] => interpreters
                    )
                    */

            }
        );
        return $controller;
    }
}
