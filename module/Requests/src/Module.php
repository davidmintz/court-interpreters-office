<?php
/**
 * module/Requests/src/Module.php.
 */

namespace InterpretersOffice\Requests;

/**
 * Module class for application's Requests module.
 */
class Module
{

    /**
     * returns this module's configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__.'/../config/module.config.php';
    }

    public function onBootstrap(\Zend\EventManager\EventInterface $event)
    {
        $container = $event->getApplication()->getServiceManager();
        $sharedEvents = $container->get('SharedEventManager');
        $log = $container->get('log');
        $em  = $container->get('entity-manager');
        /**
         * the pre.populate event is triggered in EventsController
         * edit action, after the entity is fetched and bound to the
         * form but before $form->setData($post). The EventForm needs to
         * be informed as to whether the Event was originally set from a
         * Request entity.
         *
        */
        $sharedEvents->attach(
            'InterpretersOffice\Admin\Controller\EventsController',
            'pre.populate',
            function($e) use ($log, $em){
                $log->debug(sprintf(
                    'running "pre.populate" event listener in %s',__CLASS__
                ));
                $entity_id = $e->getParams()['entity']->getId();
                //var_dump(Entity\Request::class);
                $request = $em->createQuery(
                    'SELECT r.id FROM '. Entity\Request::class
                    . ' r JOIN r.event e WHERE e.id = :event_id')
                    ->setParameters([':event_id'=>$entity_id])
                    ->getOneOrNullResult();
                if ($request) {
                    $form = $e->getParams()['form'];
                    $form->setElectronic(true);
                }
            },200
        );
    }
}
