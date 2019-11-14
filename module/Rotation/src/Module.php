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

    /*
     * ViewModel
     *
     * @var ViewModel
     */
    //private $viewModel;

    /**
     * onBootstrap listener
     *
     * @param  EventInterface $event
     * @return void
     */
    public function onBootstrap(EventInterface $event)
    {
        $container =  $event->getApplication()->getMvcEvent()->getApplication()
            ->getServiceManager();
        $auth = $container->get('auth');
        if ($auth->hasIdentity() && $auth->getIdentity()->role != 'submitter') {
            $viewModel = $event->getApplication()->getMvcEvent()
                ->getViewModel();
            $viewModel->notes_enabled = true; // by default
            $this->viewModel = $viewModel;
            $eventManager = $event->getApplication()->getEventManager();
            $eventManager->attach(MvcEvent::EVENT_RENDER,[$this,'initialize'],10);
        }

    }

    public function initialize(EventInterface $event)
    {
        $container =  $event->getApplication()->getMvcEvent()->getApplication()
            ->getServiceManager();
        $log = $container->get('log');
        $log->debug("here's Johnny in ".__METHOD__
        . "! now your job is to figure out whether to inject Task stuff into the view");
        $viewModel = $event->getApplication()->getMvcEvent()
            ->getViewModel();
        $log->debug("template? ",['template'=>$viewModel->getTemplate()]);
        $log->debug("note settings? ",['note_settings'=>$viewModel->note_settings]);

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
