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
            $eventManager->attach(MvcEvent::EVENT_RENDER,[$this,'initialize']);
        }
    }

    /**
     * initializes MOTD, MOTW (a/k/a Notes)
     *
     * @param  EventInterface $event
     */
    public function initialize(EventInterface $event) {

        if (! $event->getRouteMatch()) {
            return;
        }
        $container =  $event->getApplication()->getMvcEvent()->getApplication()
            ->getServiceManager();
        $request = $event->getRequest();
        $log = $container->get('log');
        $is_xhr = $request->isXMLHttpRequest();
        $default_date = null;
        $session = new Container('notes');
        //$session->settings = null;
        if (! $is_xhr && 'schedule' == $this->viewModel->action) {
            // take the date from the currently-displayed schedule
            $children = $this->viewModel->getChildren();
            if (count($children)) {
                $view = $children[0];
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
        }
        $service = $container->get(Service\NotesService::class);
        $route = $event->getRouteMatch()->getMatchedRouteName();
        $render_markdown = 'notes/edit' != $route;
        $log->debug("$route is our route. render markdown? ".($render_markdown ? "true":"false"));

        if ($session->settings) { // inject Notes config from session into view
            $this->viewModel->note_settings = $session->settings;
            $settings = $session->settings;
            $date = new \DateTime($settings['date']);
            // we eager-load this into the view because we know we will need it
            if ($settings['motd']['visible'] && $settings['motw']['visible']) {
                $log->debug("fetching both motd and motw for {$settings['date']}");
                $this->viewModel->setVariables($service->getAllForDate($date, $render_markdown));
            } elseif ($settings['motd']['visible'] xor $settings['motw']['visible']) {
                foreach (['motd','motw']  as $type) {
                    if ($settings[$type]['visible']) {
                        $this->viewModel->$type = $service->getNoteByDate($date,$type, $render_markdown);
                        $log->debug("Notes module bootstrap: we fetched $type for {$settings['date']}: ".gettype($this->viewModel->$type));
                        break;
                    }
                }
            } else { $log->debug("fetched neither motd nor motw for {$settings['date']}");}
            $service->setSession($session);
        } else { // inject default Notes config into view
            //$log->debug("no existing session settings for Notes");
            $defaults = Service\NotesService::$default_settings;
            $defaults['date'] = $default_date ?: date('Y-m-d');
            $this->viewModel->note_settings = $defaults;
            $session->settings = $defaults;
            //$log->debug(print_r($defaults,true));
        }

        $config = $container->get('config');
        $rotation_config = $config['rotation'] ?? null;
        if ($rotation_config && isset($rotation_config['display_rotating_assignments'])) {
            $log->debug("found config for displaying rotation in mot[dw], date is "
                . ($default_date ?: date('Y-m-d') ) );
            $display = $rotation_config['display_rotating_assignments'];
            foreach (['motd','motw'] as $note) {

            }
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
        }
        // maybe move this block up, and return early if it's true?
        if (__NAMESPACE__ == $this->viewModel->module) {
            // if we're in the Notes admin area, don't display
            $event->getApplication()->getMvcEvent()
                ->getViewModel()->display_notes = false;
        }
    }
}
