<?php
/**
 * module/Admin/src/Module.php
 *
 */

namespace InterpretersOffice\Admin;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

/**
 * Module class for our InterpretersOffice\Admin module
 */
class Module
{
    /**
     * returns this module's configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__.'/../config/module.config.php';
    }
    
    /**
     * callback to check authentication on mvc dispatch event.
     * 
     * If the routeMatch's "module" parameter is InterpretersOffice\Admin, 
     * we test for authentication and redirect to login if the user is not 
     * authenticated. Otherwise, we test whether the user is in the role 
     * "manager" or "administrator" and redirect to login if not. This last
     * is arguably something that should be handled by ACL but we are here now,
     * so why not.
     * 
     * We could have attached this listener to the MvcEvent::EVENT_ROUTE event
     * instead, which is earlier in the cycle, but could not figure out how 
     * to acquire a FlashMessenger without the Controller instance. 
     * 
     * @todo maybe inject User entity, if found, into someplace for later access.
     * e.g., the controller?
     * 
     * @param MvcEvent $event
     */
    public function enforceAuthentication(MvcEvent $event)
    {
        $match = $event->getRouteMatch();
        if (!$match) { return; }
        $module  = $match->getParam('module');
        $controller = $event->getTarget();
        if (__NAMESPACE__ == $module) {
            $container =  $event->getApplication()->getServiceManager();
            $auth = $container->get('auth');
            if (! $auth->hasIdentity()) {
                $controller->flashMessenger()
                        ->addWarningMessage("Authentication is required.");
                return $this->getRedirectionResponse($event);

            } else {

                $allowed = ['manager','administrator'];
                $user = $container->get('entity-manager')
                    ->find('InterpretersOffice\Entity\User',
                    $auth->getIdentity()->getId()
                );
                $role = (string) $user->getRole();
                if (! in_array($role, $allowed)) {
                    $controller->flashMessenger()
                        ->addWarningMessage("Access denied.");
                    return $this->getRedirectionResponse($event);
                } //else { echo "... $role: you're cool ...";}
            }
        }
    }
    
    /**
     * returns a Response redirecting to the login page.
     * 
     * @param MvcEvent $event
     * @return Zend\Http\PhpEnvironment\Response
     */
    public function getRedirectionResponse(MvcEvent $event)
    {
        $response = $event->getResponse();
        $baseUrl = $event->getRequest()->getBaseurl();
        $response->getHeaders()
            ->addHeaderLine('Location', $baseUrl.'/login');
        $response->setStatusCode(302);
        $response->sendHeaders();
        return $response;
    }
    
    /**
     * {@inheritDoc}
     * @param \Zend\EventManager\EventInterface $event
     * interesting discussion, albeit for ZF2
     * http://stackoverflow.com/questions/14169699/zend-framework-2-how-to-place-a-redirect-into-a-module-before-the-application#14170913
     */
    public function onBootstrap(\Zend\EventManager\EventInterface $event)
    {
        $eventManager = $event->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_DISPATCH,[$this,'enforceAuthentication']);
        
        $log = $event->getApplication()->getServiceManager()->get("log");
        $log->debug(sprintf("running %s at %d in %s",__FUNCTION__,__LINE__,basename(__FILE__)));
        
        //$container = $event->getApplication()->getServiceManager();
        //echo get_class($container->get("SharedEventManager"));
    }
}
