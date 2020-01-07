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

    /**
     * onBootstrap event listener
     *
     * @param  ZendEventManagerEventInterface $event
     * @return void
     */
    public function onBootstrap(\Laminas\EventManager\EventInterface $event)
    {
        $container = $event->getApplication()->getServiceManager();
        $sharedEvents = $container->get('SharedEventManager');
        $log = $container->get('log');
        $em  = $container->get('entity-manager');
        /**
         * the pre.populate event is triggered in EventsController
         * edit action, after the entity is fetched and bound to the
         * form but before $form->setData($post). We inform the Event form
         * as to whether the Event was originally set from a
         * Request entity. That it turn tells us whether the Event metadata
         * (submitted by whom and when) should be immutable.
         *
         * The logic is that the Requests module might not be enabled, in which
         * case we don't care... but this is actually kind of foolish so we
         * may need to rethink this whole thing.
         *
        */
        $sharedEvents->attach(
            'InterpretersOffice\Admin\Controller\EventsController',
            'pre.populate',
            function ($e) use ($log, $em) {

                $entity_id = $e->getParams()['entity']->getId();
                $request = $em->createQuery(
                    'SELECT r.id FROM '. Entity\Request::class
                    . ' r JOIN r.event e WHERE e.id = :event_id'
                )
                    ->setParameters([':event_id' => $entity_id])
                    ->getOneOrNullResult();
                if ($request) {
                    $form = $e->getParams()['form'];
                    $form->setElectronic(true);
                    $log->debug(sprintf(
                        'set shit = TRUE in "pre.populate" event listener in %s',
                        __CLASS__
                    ));
                }
            },
            200
        );
    }
}
