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
        $default_date = date('Y-m-d');
        $session = new Container('notes');
        // if they are loading the schedule, non-xhr...
        if (! $is_xhr && 'schedule' == $this->viewModel->action) {
            // ...then we take the date from the currently-displayed schedule
            $children = $this->viewModel->getChildren();
            if ($children)  {
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
        $render_notes = false;
        if ($session->settings) { // inject Notes configuration and data from session into view
            $session->settings['date'] = $default_date;
            $this->viewModel->note_settings = $session->settings;
            $settings = $session->settings;
            $date = new \DateTime($default_date);
            if (! $is_xhr) {
                // eager-load this into the view because we know we will need it
                if ($settings['motd']['visible'] && $settings['motw']['visible']) {
                    $render_notes = true;
                    $log->debug("fetching both motd and motw for {$settings['date']}");
                    $this->viewModel->setVariables($service->getAllForDate($date, $render_markdown));
                } elseif ($settings['motd']['visible'] xor $settings['motw']['visible']) {
                    $render_notes = true;
                    foreach (['motd','motw']  as $type) {
                        if ($settings[$type]['visible']) {
                            $this->viewModel->$type = $service->getNoteByDate($date,$type, $render_markdown);
                            $log->debug("Notes module bootstrap: we fetched $type for {$settings['date']}: "
                                .gettype($this->viewModel->$type));
                            if (!$this->viewModel->$type) :
                                /** @todo conditionally fetch task data into view */
                                $log->debug("$type is null, need fetch task data if applicable?");
                            endif;
                            break;
                        }
                    }
                } else {
                    $log->debug("fetched neither motd nor motw for {$settings['date']}");
                }
                if ($render_notes) {
                    // i.e., we are rendering a view that includes MOT[DW]
                    // and so give the Rotations listener an opportunity
                    $events = $event->getApplication()->getEventManager();
                    $events->addIdentifiers(['Notes']);
                    $events->trigger('NOTES_RENDER','Notes',compact('event','date','settings'));
                }
                $service->setSession($session);
            }

        } else { // inject default Notes config (and not data) into view

            $defaults = Service\NotesService::$default_settings;
            $defaults['date'] = $default_date ?: date('Y-m-d');
            $this->viewModel->note_settings = $defaults;
            $session->settings = $defaults;

        }


        // maybe move this block up, and return early if it's true?
        if (__NAMESPACE__ == $this->viewModel->module) {
            // if we're in the Notes admin area, don't display
            $event->getApplication()->getMvcEvent()
                ->getViewModel()->display_notes = false;
        }
    }
}
