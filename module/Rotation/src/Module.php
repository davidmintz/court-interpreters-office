<?php
/** module/Notes/src/Module.php */

namespace InterpretersOffice\Admin\Rotation;

use Laminas\EventManager\EventInterface;
use Laminas\View\Model\JsonModel;

/**
 * Module class for our InterpretersOffice\Admin\Rotation module.
 * 
 * This module is concerned with managing the rotation of 
 * recurring tasks
 */
class Module {
    /**
     * returns this module's configuration.
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__.'/../config/module.config.php';
    }

    /**
     * onBootstrap listener
     * 
     * @param  EventInterface $event
     * @return void
     */
    public function onBootstrap(EventInterface $event)
    {
        $app = $event->getApplication();
        $container =  $app->getMvcEvent()->getApplication()
            ->getServiceManager();
        $auth = $container->get('auth');
        //$log = $container->get('log');
        if ($auth->hasIdentity() && $auth->getIdentity()->role != 'submitter') {

            $app->getEventManager()->getSharedManager()
                ->attach('Notes','NOTES_RENDER',
                [ $container->get(Service\TaskRotationService::class),
                'initializeView']);
                //$log->debug("we have attached NOTES_RENDER listener in ".__METHOD__);
        }
        // $eventManager = $event->getApplication()->getEventManager();
        
    }

    /**
     * Conditionally injects Rotation data into view.
     *
     * Listener for NOTES_RENDER (MOT[DW]) injects Rotation (Task)
     * data into the view. Then we can display the name of person 
     * designated for a task on the date of the MOT[DW].
     *
     * @param  EventInterface $event
     * @return void
     */
    public function _initializeView(EventInterface $e)
    {

        $mvcEvent = $e->getParam('event');
        $date = $e->getParam('date');
        $container =  $mvcEvent->getApplication()->getServiceManager();
        $log = $container->get('log');
        $log->debug("heeeeeeeeere's Johnny in ".__METHOD__ . " where shit was triggered");
        $rotation_config = $container->get('config')['rotation'] ?? null;
        if (! $rotation_config or !isset($rotation_config['display_rotating_assignments'])) {
            $log->debug("no task-rotation config, returning");
            return;
        }
        $note_types = $e->getParam('note_types',[]);
        if (! $note_types) {
            $settings = $e->getParam('settings');
            foreach (['motd','motw'] as $type) {
                if ($settings[$type]['visible']) {
                    $note_types[] = $type;
                }
            }
        }

        $service = $container->get(Service\TaskRotationService::class);
        $assignment_notes = $service->getAssignmentsForNotes($note_types,$date);
        if ($assignment_notes) {
            $log->debug(count($assignment_notes) . ' assignment notes found');
            $view = $e->getParam('view') ?:
                $mvcEvent->getApplication()->getMvcEvent()->getViewModel();
            if ($view instanceof JsonModel) {
                $log->debug("HA! need to inject JSON data");
                $view->assignment_notes = json_encode(['foo' => 'boink']);
            } else {
                $view->assignment_notes = $assignment_notes;
            }
        }
    }
}
