<?php
/** module/Notes/src/Module.php */

namespace InterpretersOffice\Admin\Rotation;

use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\ViewModel;
use Zend\Session\Container;
use function \date;
/**
 * Module class for our InterpretersOffice\Admin\Rotation module.
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
     * @todo decide if we're going to go this way or not. see below.
     * @param  EventInterface $event
     * @return void
     */
    public function onBootstrap(EventInterface $event)
    {
        $container =  $event->getApplication()->getMvcEvent()->getApplication()
            ->getServiceManager();
        $auth = $container->get('auth');
        if ($auth->hasIdentity() && $auth->getIdentity()->role != 'submitter') {
            $event->getApplication()->getEventManager()->getSharedManager()
                ->attach('Notes','NOTES_RENDER',[$this,'initializeView']);
            $log = $container->get('log');
            $log->debug("attached NOTES_RENDER listener in ".__METHOD__);
        }
    }

    /**
     * Conditionally injects Rotation data into view.
     *
     * Listener for NOTES_RENDER (MOT[DW]) inject s Rotation (Task)
     * data into the view. The disadvantage is it won't work for xhr
     * requests.  or does it?
     *
     * @param  EventInterface $event
     * @return void
     */
    public function initializeView(EventInterface $e)
    {

        $mvcEvent = $e->getParam('event');
        $date = $e->getParam('date');
        $settings = $e->getParam('settings');
        $container =  $mvcEvent->getApplication()->getServiceManager();
        $log = $container->get('log');
        $log->debug("here's Johnny in ".__METHOD__ . " where shit was triggered");
        $log->debug("inject Task stuff into the view?");
        // $viewModel = $mvcEvent->getApplication()->getMvcEvent()
        //     ->getViewModel();
        // $log->debug("template? ",['template'=>$viewModel->getTemplate()]);
        $rotation_config = $container->get('config')['rotation'] ?? null;
        if (! $rotation_config or !isset($rotation_config['display_rotating_assignments'])) {
            $log->debug("no task-rotation config, returning");
            return;
        }
        $note_types = [];
        foreach (['motd','motw'] as $type) {
            if ($settings[$type]['visible']) {
                $note_types[] = $type;
            }
        }
        $service = $container->get(Service\TaskRotationService::class);
        $assignment_notes = $service->getAssignmentsForNotes($note_types,$date);
        if ($assignment_notes) {
            $log->debug(count($assignment_notes) . ' assignment notes found');
            $viewModel = $mvcEvent->getApplication()->getMvcEvent()->getViewModel();
            $viewModel->assignment_notes = $assignment_notes;
            // $view = $viewModel->getChildren()[0] ?? null;
            // if ($view) {
            //     $view->assignment_notes = $assignment_notes;
            //     $log->warn("FUCK? shit was assigned to ".$view->getTemplate());
            // } else {
            //     $log->warn("FUCK?");
            // }
        }
    }
}
/*
// temporary scratch area ===========

//should we inject task-rotation stuff?
$config = $container->get('config');
$rotation_config = $config['rotation'] ?? null;
if ($render_notes && $rotation_config && isset($rotation_config['display_rotating_assignments'])) {
    $log->debug("found config for displaying rotation in mot[dw], date is "
        . ($default_date ?: date('Y-m-d') ) );
    $task_config = $rotation_config['display_rotating_assignments'];
    foreach (['motd','motw'] as $note_type) {
        if (!$settings[$note_type]['visible']) {
            $log->debug("$note_type display is off, moving on...");
            continue;
        }
        if (isset($task_config[$note_type])) {
            if (! is_array($task_config[$note_type])) {
                throw new \RuntimeException(
                    "Invalid configuration for Rotation module. Each entry under 'display_rotating_assignments' should be an array.
                    Please check your configuration in module/Rotation/config/config.json or through the web interface"
                );
            }
            foreach($task_config[$note_type] as $task_id) {
                $date = $default_date ?: date('Y-m-d');
                $log->debug("need to fetch assignment for task id $task_id for $note_type");
                // this definitely needs to be factored out
                $repo = $container->get('entity-manager')
                    ->getRepository(\InterpretersOffice\Admin\Rotation\Entity\Task::class);
                $task = $repo->find($task_id);
                if (! $task) {
                    $log->warn("task id not found",['method'=>__METHOD__,]);
                } else {
                    $shit = $repo->getAssignedPerson($task, new \DateTime($date));
                    $log->debug(
                        sprintf(
                            'task is %s; assigned: %s; default: %s',
                                $task->getName(),
                                $shit['assigned']->getFirstName(),
                                $shit['default']->getFirstName()
                            )
                    );
                }
            }
        }
    }*/
    /** @todo now get the current rotation-assignments and inject into view */
    /*[display_rotating_assignments] => Array
(
    [motd] => Array
        (
            [0] => 2
        )

    [motw] => Array
        (
            [0] => 1
        )

        )
    */
//}
