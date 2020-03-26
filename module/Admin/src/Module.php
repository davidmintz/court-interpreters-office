<?php
/**
 * module/Admin/src/Module.php.
 */

namespace InterpretersOffice\Admin;

use Laminas\Mvc\MvcEvent;
use Laminas\Session\SessionManager;

use InterpretersOffice\Admin\Controller;
use InterpretersOffice\Entity\Listener\EventEntityListener;
use InterpretersOffice\Admin\Service\Log\Writer as DbWriter;

use Laminas\Uri\UriFactory;

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
     * @param \Laminas\EventManager\EventInterface $event
     * interesting discussion, albeit for ZF2
     * http://stackoverflow.com/questions/14169699/zend-framework-2-how-to-place-a-redirect-into-a-module-before-the-application#14170913
     */
    public function onBootstrap(\Laminas\EventManager\EventInterface $event)
    {
        $container = $event->getApplication()->getServiceManager();
       
        $log = $container->get('log');
        
        //$log->addWriter($container->get(DbWriter::class));
        /*
         * for TEMPORARY debugging
         */
         // ===============
        //  
         //==============
        // set the "breadcrumbs" navigation view-helper separator
        // unless there's a better way to make sure this gets done globally...
        $navigation = $container->get('ViewHelperManager')->get("navigation");
        $navigation->setDefaultAcl($container->get('acl'));
        $navigation->findHelper('breadcrumbs')->setSeparator(' | ');
        $config = $container->get('config');
        $viewModel = $event->getApplication()->getMvcEvent()
            ->getViewModel();
        if (isset($config['site'])) {
            $viewModel->setVariables($config['site']['contact']);
        }
        // workaround for phpunit and php7.2 which is less tolerant than earlier
        // php versions and throws "ini_set(): Headers already sent. You cannot
        // change the session module's ini settings at this time"
        if ('testing' != getenv('environment')) {
            $container->get(SessionManager::class);
        }

        /** catch  Laminas\Session\Exception\RuntimeException validation failure */
        try {
            // try something a little different
            $auth = $container->get('auth');
            if ($auth->hasIdentity()) {
                $user = $container->get('auth')->getIdentity();
                if ($user) {
                    $viewModel->user = $user;
                    $navigation->setDefaultRole($user->role);
                    if (in_array($user->role, ['administrator','manager','staff',])) {
                        $viewModel->navigation_menu = 'default';
                    } elseif ('submitter' == $user->role) {
                        $viewModel->navigation_menu = 'Laminas\Navigation\Requests';
                    }
                }
            } else {
                $user = null;
            }
        } catch (\Laminas\Session\Exception\RuntimeException $e) {
             return $this->getRedirectionResponse($event);
        }
        $db_writer = $container->get(DbWriter::class);
        $log = $container->get('log');
        $log->debug("\n----------------------------------------\n");
        $eventManager = $event->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_ROUTE, function($e) use ($log,$db_writer){
            $log->addWriter($db_writer);
        });
        // desperation shot. note to self: learn how to do this properly.
        // I'm sick of getting the HTML error page when there's an error 
        // in response to an xhr request
        /*$json_error = function($e){
            $container = $e->getApplication()->getServiceManager();
            $request = $container->get('Request');
            $response = $container->get('Response');
            if ($request->isXmlHttpRequest()) {                
                $exception = $e->getParam("exception");
                if ($exception) {
                    $data = [
                        'message' => $exception->getMessage(),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                        'code' => $exception->getCode(),
                        'class' => get_class($exception),
                        'uri'  => (string)$request->getUri()
                    ];
                } else {
                    $data = [
                        'message' => 'unexpected error encounted, no exception available',
                        'uri'  => (string)$request->getUri(),
                    ];
                }                
                $response->getHeaders()
                ->addHeaderLine('Content-type', 'application/json');
                $response->setContent(json_encode($data));
                $response->sendHeaders();
                exit((string)$response->getContent());
            }
        };*/
        // $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR,$json_error);
        // $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR,$json_error);
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR,[$this,'logError']);
        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR,[$this,'logError']);
        $eventManager->attach(MvcEvent::EVENT_ROUTE, [$this, 'enforceAuthentication']);
        $eventManager->attach(MvcEvent::EVENT_ROUTE, function ($event) use ($viewModel)
        {
            $routeMatch = $event->getRouteMatch();
            if ($routeMatch) {
                $viewModel->setVariables($routeMatch->getParams());
                // really? not sure why we need this...
                $viewModel->routeMatch = $routeMatch->getMatchedRouteName();
            }
        });
        $sharedEvents = $container->get('SharedEventManager');
        $log = $container->get('log');
        /** @todo move this to the ScheduleUpdateManagerFactory */
        /** @var  InterpretersOffice\Service\ScheduleUpdateManager $scheduleManager */
        $scheduleManager = $container->get('InterpretersOffice\Admin\Service\ScheduleUpdateManager');
        $sharedEvents->attach(
            '*', // maybe narrow down "*" to something more specific?
            'loadRequest',
            function ($e) use ($log, $scheduleManager) {
                $params = $e->getParams();
                $entity = $params['entity'];
                $log->debug("setting previous state in loadRequest event-listener,
                 ".gettype($entity) . " is type of our entity");
                $scheduleManager->setPreviousState($entity);
            }
        );
        // the Request write-controller triggers this following flush. This is
        // designed to avoid the possibility of sending emails that say some
        // database updates were run when, in the event of an Exception, they
        // really weren't.
        $sharedEvents->attach(
            '*','postFlush', [$scheduleManager,'dispatchEmail']
        );
        /*  -------------------------------------------------------------    */
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
        $request = $event->getRequest();
        // temporary
        //$channel = ['channel'=>'redirect-debug'];
        //$log = $container->get('log');
        $request_uri = $request->getUri();
        if (! $auth->hasIdentity()) {
            // everything else requires authentication
            $flashMessenger = $container
                    ->get('ControllerPluginManager')->get('FlashMessenger');
            $flashMessenger->addWarningMessage('Authentication is required.');
            // 'session_containers' => [...] config lets you get away with this:
            // $session = $container->get('Authentication') ;
            // except that phpunit tests blow up.
            $session = new \Laminas\Session\Container('Authentication');
            $is_xhr = $request->isXmlHttpRequest();
            if ($is_xhr) {
                $http_referrer = $request->getServer()->get('HTTP_REFERER');
                if ($http_referrer) {
                    $referrer = UriFactory::factory($http_referrer);
                    if ($referrer->getHost() == $request_uri->getHost()) {
                        $session->redirect_url = $referrer->getPath()?:"/";
                    }
                }
            } else {
                $session->redirect_url = (string)$request_uri;
            }
            $allowed = false;
        } else {
            // check authorization
            $user = $auth->getIdentity();
            $role = $user->role;
            if (! $this->checkAcl($event, $role)) {
                $flashMessenger = $container
                    ->get('ControllerPluginManager')->get('FlashMessenger');
                $flashMessenger->addWarningMessage('Your user account is not authorized to access the requested resource.');
                $allowed = false;
            }
        }
        if (! $allowed) {
             $container->get("log")->debug("WTF? redirecting...");
             return $this->getRedirectionResponse($event);
        }
        /** try to prevent us from timing out */
        $session = new \Laminas\Session\Container('Authentication');
        if (! $session->last_access or $session->last_access < time() - 60) {
            $session->last_access = time();
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
        $privilege = $match->getParam('action', \strtolower($event->getRequest()->getMethod()));
        $log = $event->getApplication()->getServiceManager()->get('log');
        // $log->warn("WTF? action is '$privilege'");
        $log->debug(
            sprintf(__METHOD__." checking role %s access to resource %s, privilege %s",
            $role, is_object($resource) ? get_class($resource):$resource, $privilege
            )
        );
        $acl = $event->getApplication()->getServiceManager()->get('acl');
        return $acl->isAllowed($role, $resource, $privilege);
    }

    public function logError(MvcEvent $event)
    {
        $container = $event->getApplication()->getServiceManager();
        $log = $container->get('log');
        if ($event->getParam('exception')) {
            $exception = $event->getParam('exception');           
            $message = $exception->getMessage();
            if ($event->getParam('details')) {
                $message .= sprintf(
                    "details: %s\n",
                    is_string($event->getParam('details')) ?
                        $event->getParam('details')
                    : json_encode($event->getParam('details'))
                );
            }
            $stacktrace =  $exception->getTraceAsString();
            $previous = '';
            do {
                $previous .= sprintf(
                    "%s:%d %s (%d) [%s]\n",
                    $exception->getFile(),
                    $exception->getLine(),
                    $exception->getMessage(),
                    $exception->getCode(),
                    get_class($exception)
                );
            } while ($exception = $exception->getPrevious());
            $context = ['stacktrace'=> $stacktrace,'channel'=> 'error'];
            $context['event'] = $event->getName();
            if ($previous) {
                $context['previous'] = $previous;
            }
            $log->err($message,$context);
        }
    }


    /**
     * returns a Response redirecting to the login page.
     *
     * @param MvcEvent $event
     *
     * @return Laminas\Http\PhpEnvironment\Response
     */
    public function getRedirectionResponse(MvcEvent $event)
    {
        $container = $event->getApplication()->getServiceManager();
        $log = $container->get('log')->debug("this is ".__METHOD__);
        $response = $event->getResponse();
        $baseUrl = $event->getRequest()->getBaseurl();
        $response->getHeaders()
            ->addHeaderLine('Location', $baseUrl.'/login');
        $response->setStatusCode(303);
        $response->sendHeaders();

        return $response;
    }
}
