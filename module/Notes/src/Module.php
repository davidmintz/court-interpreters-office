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
                    $log->debug("FUCK YEAH");
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

        if ($session->settings) {
            $this->viewModel->note_settings = $session->settings;
            $log->debug("setting MOTD/MOTW session values to view");
            $settings = $session->settings;
            if ($settings['motd']['visible'] && $settings['motw']['visible']) {
                $log->debug("fetch both motd and motw for {$settings['date']}");
            } elseif ($settings['motd']['visible'] xor $settings['motw']['visible']) {
                foreach (['motd','motw']  as $type) {
                    if ($settings[$type]['visible']) {
                        $log->debug("fetch $type for {$settings['date']}");
                        break;
                    }
                }
            } else {
                $log->debug("fetch neither motd nor motw for {$settings['date']}");
            }
            $container->get(Service\NotesService::class)->setSession($session);
        } else {
            $log->debug("setting MOTD/MOTW default values");
            // defaults
            $defaults = Service\NotesService::$default_settings;
            $defaults['date'] = $default_date ?: date('Y-m-d');
            $this->viewModel->note_settings = $defaults;
        }
    }
}
