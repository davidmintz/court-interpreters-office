<?php
/**
 * module/Admin/src/Module.php.
 */

namespace InterpretersOffice\Admin;

use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;

use InterpretersOffice\Admin\Controller;

//use InterpretersOffice\Controller;
/**
 * Module class for our InterpretersOffice\Admin module.
 */
class Module
{

    /**
     * are we authenticated?
     *
     * @var booleam
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
        // $view = $container->get('ViewRenderer'); var_dump(get_class($view));
        // set the "breadcrumbs" navigation view-helper separator
        // unless there's a better way to make sure this gets done globally...
        $navigation = $container->get('ViewHelperManager')->get("navigation");
        $navigation->setDefaultAcl($container->get('acl'));
        $navigation->findHelper('breadcrumbs')->setSeparator(' | ');
        $user = $container->get('auth')->getIdentity();
        if ($user) {
            $navigation->setDefaultRole($user->role);            
        }
        
        $eventManager = $event->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_ROUTE, [$this, 'enforceAuthentication']);
        //$eventManager->attach(MvcEvent::EVENT_ROUTE, [$this,'attachEntityListener']);
        $eventManager->attach(MvcEvent::EVENT_ROUTE, function($event) use ($user) {
            $routeMatch = $event->getRouteMatch();
            if ($routeMatch) {
                $viewModel = $event->getApplication()->getMvcEvent()
                        ->getViewModel();
                $viewModel->setVariables($routeMatch->getParams());
                $viewModel->user = $user;
            }
        });
        
        // The following line instantiates the SessionManager and automatically
        // makes the SessionManager the 'default' one:
        // https://olegkrivtsov.github.io/using-zend-framework-3-book/html/en/Working_with_Sessions/Session_Manager.html
        $container->get(SessionManager::class);// yes. just the getting is enough
      
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
        if ( 'InterpretersOffice' == $module ) {
             // doesn't expose anything, so anyone is allowed access
             return;             
        }
        $allowed = true;
        $container = $event->getApplication()->getServiceManager();
        $auth = $container->get('auth');
        if (! $auth->hasIdentity() ) {
            // everything else requires authentication
            $flashMessenger = $container
                    ->get('ControllerPluginManager')->get('FlashMessenger');
            $flashMessenger->addWarningMessage('Authentication is required.');
            $session = $container->get('Authentication'); 
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
        
        $controllerFQCN = $match->getParam('controller');
        // really ?
        $controllerName = substr($controllerFQCN, strrpos($controllerFQCN, '\\') + 1, -10);
        $resource = strtolower((new \Zend\Filter\Word\CamelCaseToDash)->filter($controllerName));
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
