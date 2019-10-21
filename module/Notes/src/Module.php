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
            $viewModel->notes_enabled = true;
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

        $container =  $event->getApplication()->getMvcEvent()->getApplication()
            ->getServiceManager();
        $request = $event->getRequest();
        if ($request->isXMLHttpRequest()) {
            return;
        }
        $log = $container->get('log');
        $default_date = null;
        $session = new Container('notes');
        //$session->settings = null;
        if ('schedule' == $this->viewModel->action) {
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
        if ($session->settings) { // inject Notes config from session into view
            $this->viewModel->note_settings = $session->settings;
            $settings = $session->settings;
            $date = new \DateTime($settings['date']);
            if ($settings['motd']['visible'] && $settings['motw']['visible']) {
                $this->viewModel->setVariables($service->getAllForDate($date));
                $log->debug("fetched both motd and motw for {$settings['date']}");
            } elseif ($settings['motd']['visible'] xor $settings['motw']['visible']) {
                foreach (['motd','motw']  as $type) {
                    if ($settings[$type]['visible']) {
                        $this->viewModel->$type = $service->getNoteByDate($date,$type);
                        $log->debug("fetched $type for {$settings['date']}: ".gettype($this->viewModel->$type));
                        break;
                    }
                }
            } // else {log->debug("fetch neither motd nor motw for {$settings['date']}");}
            $service->setSession($session);
        } else { // inject default Notes config into view
            //$log->debug("no existing session settings for Notes");
            $defaults = Service\NotesService::$default_settings;
            $defaults['date'] = $default_date ?: date('Y-m-d');
            $this->viewModel->note_settings = $defaults;
            $session->settings = $defaults;
            //$log->debug(print_r($defaults,true));
        }
    }
}
