<?php
/**
 * module/Admin/src/Module.php.
 */

namespace InterpretersOffice\Admin;

use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;

use InterpretersOffice\Admin\Controller;
use InterpretersOffice\Entity\Listener\EventEntityListener;

//use InterpretersOffice\Controller;
/**
 * Module class for our InterpretersOffice\Admin module.
 */
class Module
{

    /**
     * are we authenticated?
     *
     * @var boolean
     */
    protected $authenticated = false;

    /**
     * returns this module's configuration.
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__.'/../config/module.config.php';
    }

    /**
     * {@inheritdoc}
     *
     * @param \Zend\EventManager\EventInterface $event
     * interesting discussion, albeit for ZF2
     * http://stackoverflow.com/questions/14169699/zend-framework-2-how-to-place-a-redirect-into-a-module-before-the-application#14170913
     */
    public function onBootstrap(\Zend\EventManager\EventInterface $event)
    {


        $container = $event->getApplication()->getServiceManager();

        /**
         * TEMPORARY debug
         *
         */

         // $path = 'data/log/sql.log';
         // $fp = fopen($path,'w');
         // ftruncate($fp,0);
         // fclose($fp);
         // $log = new \Zend\Log\Logger();
         // $log->addWriter(new \Zend\Log\Writer\Stream($path,'a'));
         // $sql_logger = new \InterpretersOffice\Service\SqlLogger($log);
         // $em = $container->get('entity-manager');
         // $em->getConfiguration()->setSQLLogger($sql_logger);
         //==============


        // $view = $container->get('ViewRenderer'); var_dump(get_class($view));
        // set the "breadcrumbs" navigation view-helper separator
        // unless there's a better way to make sure this gets done globally...
        $navigation = $container->get('ViewHelperManager')->get("navigation");
        $navigation->setDefaultAcl($container->get('acl'));
        $navigation->findHelper('breadcrumbs')->setSeparator(' | ');
        // workaround for phpunit and php7.2 which is less tolerant than earlier
        // php versions and throws "ini_set(): Headers already sent. You cannot
        // change the session module's ini settings at this time"
        if ('testing' != getenv('environment')) {
            $container->get(SessionManager::class);
        }
        $user = $container->get('auth')->getIdentity();
        if ($user) {
            $navigation->setDefaultRole($user->role);
        }

        $eventManager = $event->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_ROUTE, [$this, 'enforceAuthentication']);
        $eventManager->attach(MvcEvent::EVENT_ROUTE, function ($event) use ($user,$container) {
            $routeMatch = $event->getRouteMatch();
            if ($routeMatch) {
                $viewModel = $event->getApplication()->getMvcEvent()
                        ->getViewModel();
                $viewModel->setVariables($routeMatch->getParams());
                $config = $container->get('config');
                if (isset($config['site'])) {
                    $viewModel->setVariables($config['site']['contact']);
                }
                $viewModel->routeMatch = $routeMatch->getMatchedRouteName();
                $viewModel->user = $user;
                if (! $user) {
                    return;
                }
                // figure out proper navigation bar
                if (in_array($user->role, ['administrator','manager','staff',])) {
                    $viewModel->navigation_menu = 'default';
                } elseif ('submitter' == $user->role) {
                    $viewModel->navigation_menu = 'Zend\Navigation\Requests';
                }
            }
        });

        $sharedEvents = $container->get('SharedEventManager');
        $log = $container->get('log');
        $sharedEvents->attach(
            '*',
            'error',
            function ($event) use ($log) {
                if ($event->getParam('exception')) {
                    $exception = $event->getParam('exception');
                    $message = "system error was triggered!\n";
                    if ($event->getParam('details')) {
                        $message .= sprintf(
                            "details: %s\n",
                            $event->getParam('details')
                        );
                    }
                    $trace = $exception->getTraceAsString();
                    do {
                        $message .= sprintf(
                            "%s:%d %s (%d) [%s]\n",
                            $exception->getFile(),
                            $exception->getLine(),
                            $exception->getMessage(),
                            $exception->getCode(),
                            get_class($exception)
                        );
                    } while ($exception = $exception->getPrevious());
                    $message .= sprintf("stack trace:\n%s", $trace);
                    $log->err($message);
                }
            }
        );

        // experimental.
        /** @var  InterpretersOffice\Service\ScheduleUpdateManager $scheduleManager */
        $scheduleManager = $container->get('InterpretersOffice\Admin\Service\ScheduleUpdateManager');
        $sharedEvents->attach(
            //'ENTITY_UPDATE',
            //'InterpretersOffice\Entity\Listener\EventEntityListener',
            '*',
            'loadRequest',
            function($e) use ($log,$scheduleManager) {

                $params = $e->getParams();
                // $args = $params['args'];
                $entity = $params['entity'];
                $log->debug("doing shit here and now, in event listener, ".gettype($entity) . " is type of our entity");
                $scheduleManager->setPreviousState($entity);


            }
        );
    }


    /**
     * callback to check authentication on mvc route event.
     *
     * If the routeMatch's "module" parameter is InterpretersOffice\Admin,
     * we test for authentication and redirect to login if the user is not
     * authenticated. Otherwise, we test whether the user is in the role
     * "manager" or "administrator" and redirect to login if not. This last
     * is arguably something that should be handled by ACL but we are here now,
     * so why not.
     *
     * @param MvcEvent $event
     */
    public function enforceAuthentication(MvcEvent $event)
    {
        $match = $event->getRouteMatch();
        if (! $match) {
            return;
        }
        $module = $match->getParam('module');
        if ('InterpretersOffice' == $module) {
             // doesn't expose anything, so anyone is allowed access
             return;
        }
        $allowed = true;
        $container = $event->getApplication()->getServiceManager();
        $auth = $container->get('auth');
        if (! $auth->hasIdentity()) {
            // everything else requires authentication
            $flashMessenger = $container
                    ->get('ControllerPluginManager')->get('FlashMessenger');
            $flashMessenger->addWarningMessage('Authentication is required.');
            // 'session_containers' => [...] config lets you get away with this:
            // $session = $container->get('Authentication') ;
            // except that phpunit tests blow up.
            $session = new \Zend\Session\Container('Authentication');
            $session->redirect_url = $event->getRequest()->getUriString();
            $allowed = false;
        } else {
            // check authorization
            $user = $auth->getIdentity();
            $role = $user->role;
            if (! $this->checkAcl($event, $role)) {
                $flashMessenger = $container
                    ->get('ControllerPluginManager')->get('FlashMessenger');
                $flashMessenger->addWarningMessage('Access denied.');
                $allowed = false;
            }
        }
        if (! $allowed) {
             return $this->getRedirectionResponse($event);
        }
    }
    /**
     * checks authorization
     *
     * @param MvcEvent $event
     * @param string $role
     * @return boolean true if current user is authorized access to current resource
     *
     * @todo consider changing the way controller-resources are named, e.g., use
     * FQCN instead so that the short name does not have to be unique. And let
     * each module config have its own 'acl'=> [...]
     */
    public function checkAcl(MvcEvent $event, $role)
    {

        $match = $event->getRouteMatch();
        if (! $match) {
            return;
        }

        $resource  = $match->getParam('controller');
        // really ?
        //$controllerName = substr($controllerFQCN, strrpos($controllerFQCN, '\\') + 1, -10);
        //$resource = strtolower((new \Zend\Filter\Word\CamelCaseToDash)->filter($controllerName));
        $privilege = $match->getParam('action');
        $acl = $event->getApplication()->getServiceManager()->get('acl');
        return $acl->isAllowed($role, $resource, $privilege);
    }

    /**
     * returns a Response redirecting to the login page.
     *
     * @param MvcEvent $event
     *
     * @return Zend\Http\PhpEnvironment\Response
     */
    public function getRedirectionResponse(MvcEvent $event)
    {
        $response = $event->getResponse();
        $baseUrl = $event->getRequest()->getBaseurl();
        $response->getHeaders()
            ->addHeaderLine('Location', $baseUrl.'/login');
        $response->setStatusCode(303);
        $response->sendHeaders();

        return $response;
    }
}
