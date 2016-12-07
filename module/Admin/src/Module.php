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
                $role = (string)$user->getRole();
                if (! in_array($role, $allowed)) {
                    $controller->flashMessenger()
                        ->addWarningMessage("Access denied.");
                    return $this->getRedirectionResponse($event);
                } //else { echo "... $role: you're cool ...";}
            }
        }
    }
    
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
     * interesting discussion, albeit for ZF2
     * http://stackoverflow.com/questions/14169699/zend-framework-2-how-to-place-a-redirect-into-a-module-before-the-application#14170913
     */
    public function onBootstrap(\Zend\EventManager\EventInterface $event)
    {
        
        
        $eventManager        = $event->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        if (! $event->getRequest() instanceof \Zend\Http\PhpEnvironment\Request)  {
            return;    
        }
        $eventManager->attach(MvcEvent::EVENT_DISPATCH,[$this,'enforceAuthentication']);
        return;
        $container =  $event->getTarget()->getServiceManager();
        
        
        if ($auth->hasIdentity()) {
            $user = $container->get('entity-manager')->find('InterpretersOffice\Entity\User',
                $auth->getIdentity()->getId());
        }
        //  Assuming your login route has a name 'login', this will do the assembly
            // (you can also use directly $url=/path/to/login)
        $url = $e->getRouter()->assemble(array(), array('name' => 'login'));
        $response=$e->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode(302);
        $response->sendHeaders();
            // When an MvcEvent Listener returns a Response object,
            // It automatically short-circuit the Application running 
            // -> true only for Route Event propagation see Zend\Mvc\Application::run

            // To avoid additional processing
            // we can attach a listener for Event Route with a high priority
            $stopCallBack = function($event) use ($response){
                $event->stopPropagation();
                return $response;
            };
            //Attach the "break" as a listener with a high priority
            $e->getApplication()->getEventManager()->attach(MvcEvent::EVENT_ROUTE, $stopCallBack,-10000);
            return $response;
    }
}