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
        $sharedEvents->attach(
            'InterpretersOffice\Entity\Listener\EventEntityListener',
            'postLoad',
            function($e) use ($log) {

                $params = $e->getParams();
                $args = $params['args'];
                $entity = $params['entity'];
                $id = $entity->getId();
                $em = $args->getObjectManager();
                $class = get_class($entity);
                $view_before = $em->getRepository(get_class($entity))->getView($entity->getId());
                // $view_before = [
                //     'date'=>$entity->getDate(),
                //     'time'=>$entity->getTime(),
                //     'end_time'=>$entity->getEndTime(),
                //     'last_updated'=>$entity->getModified(),
                //     // etc for all the fields in the view
                // ];
                 /* we really need...
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
                    [26] => submitter_comments
                    [27] => defendants
                    [28] => interpreters*/

                $session = new \Zend\Session\Container("event_updates");
                $session->$id = $view_before;
                $log->debug("stored entity state in session {$session->getName()}"
                     ." (id $id) for later reference");
            }
        );


        return $controller;
    }
}
