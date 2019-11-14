<?php
/** module/Notes/src/Module.php */

namespace InterpretersOffice\Admin\Notes;

use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\ViewModel;
use Zend\Session\Container;
use InterpretersOffice\Admin\Notes\Service\NotesService;
use function \date;
/**
 * Module class for our InterpretersOffice\Admin\Notes module.
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
     * ViewModel
     *
     * @var ViewModel
     */
    private $viewModel;

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
            $eventManager->attach(MvcEvent::EVENT_RENDER,[$this,'initialize'],100);
        }
    }

    /**
     * initializes MOTD, MOTW (a/k/a Notes) on MvcEvent::EVENT_RENDER
     *
     * @param  EventInterface $event
     */
    public function initialize(EventInterface $event) {

        if (! $event->getRouteMatch()) {
            return;
        }
        $container =  $event->getApplication()->getMvcEvent()->getApplication()
            ->getServiceManager();
        $log = $container->get('log');
        $log->debug("here's Johnny! ".__METHOD__);
        $is_xhr = $event->getRequest()->isXMLHttpRequest();
        $default_date = null;
        $session = new Container('notes');
        // if they are loading the schedule, non-xhr...
        if (! $is_xhr && 'schedule' == $this->viewModel->action) {
            // ...then we take the date from the currently-displayed schedule
            $view =  $this->viewModel->getChildren()[0];
            if ($view->date) {
                $default_date = $view->date->format('Y-m-d');
                if (!$session->settings) {
                    $session->settings = array_merge(
                        Service\NotesService::$default_settings,
                        ['date' => $default_date]
                    );
                } else {
                    $session->settings['date'] = $default_date;
                }
            }
        }
        $service = $container->get(Service\NotesService::class);
        $route = $event->getRouteMatch()->getMatchedRouteName();
        $render_markdown = 'notes/edit' != $route;
        $log->debug("$route is our route. render markdown? ".($render_markdown ? "true":"false"));
        $render_notes = false;
        if ($session->settings) { // inject Notes configuration and data from session into view
            $this->viewModel->note_settings = $session->settings;
            $settings = $session->settings;
            $date = new \DateTime($settings['date']);
            if (! $is_xhr) {
                // we eager-load this into the view because we know we will need it
                if ($settings['motd']['visible'] && $settings['motw']['visible']) {
                    $render_notes = true;
                    $log->debug("fetching both motd and motw for {$settings['date']}");
                    $this->viewModel->setVariables($service->getAllForDate($date, $render_markdown));
                } elseif ($settings['motd']['visible'] xor $settings['motw']['visible']) {
                    $render_notes = true;
                    foreach (['motd','motw']  as $type) {
                        if ($settings[$type]['visible']) {
                            $this->viewModel->$type = $service->getNoteByDate($date,$type, $render_markdown);
                            $log->debug("Notes module bootstrap: we fetched $type for {$settings['date']}: ".gettype($this->viewModel->$type));
                            break;
                        }
                    }
                    /*
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

                } else { $log->debug("fetched neither motd nor motw for {$settings['date']}");}
                $service->setSession($session);
            }

        } else { // inject default Notes config (and no data) into view
            //$log->debug("no existing session settings for Notes");
            $defaults = Service\NotesService::$default_settings;
            $defaults['date'] = $default_date ?: date('Y-m-d');
            $this->viewModel->note_settings = $defaults;
            $session->settings = $defaults;
            //$log->debug(print_r($defaults,true));
        }


        // maybe move this block up, and return early if it's true?
        if (__NAMESPACE__ == $this->viewModel->module) {
            // if we're in the Notes admin area, don't display
            $event->getApplication()->getMvcEvent()
                ->getViewModel()->display_notes = false;
        }
    }
}
