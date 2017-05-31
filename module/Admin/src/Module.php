<?php
/**
 * module/Admin/src/Module.php.
 */

namespace InterpretersOffice\Admin;

use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;

use InterpretersOffice\Entity\User;

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
        $eventManager = $event->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_ROUTE, [$this, 'enforceAuthentication']);       
        $container = $event->getApplication()->getServiceManager();
        // The following line instantiates the SessionManager and automatically
        // makes the SessionManager the 'default' one:
        // https://olegkrivtsov.github.io/using-zend-framework-3-book/html/en/Working_with_Sessions/Session_Manager.html
        // $sessionManager =
        $container->get(SessionManager::class);
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
     * @todo maybe inject User entity, if found, into someplace for later access.
     * e.g., the controller?
     *
     * @param MvcEvent $event
     */
    public function enforceAuthentication(MvcEvent $event)
    {
        $match = $event->getRouteMatch();
        if (! $match) {
            return;
        }
        //return;
        $container = $event->getApplication()->getServiceManager();
        $module = $match->getParam('module');
        $session = $container->get('Authentication');// to do: rename, for clarity
        if ('InterpretersOffice' == $module) {
            return;            
        }
        $auth = $container->get('auth');
        if (! $auth->hasIdentity()) {
            $flashMessenger = $container
                    ->get('ControllerPluginManager')->get('FlashMessenger');
            $flashMessenger->addWarningMessage('Authentication is required.');
            $session->redirect_url = $event->getRequest()->getUriString();            
            return $this->getRedirectionResponse($event);
        } else {
            $em = $container->get('entity-manager');
            // same as:
            //$user = $em->find('InterpretersOffice\Entity\User',$auth->getIdentity()->getId());
            // this is inefficient. find a why to do fewer than 3 queries for this?
            // does not seem to be required. Doctrine doing it for us?
            //$em->merge($auth->getIdentity());
            $user = $auth->getIdentity(); 
            if (! $this->checkAcl($event, $user)) {
                $flashMessenger = $container
                    ->get('ControllerPluginManager')->get('FlashMessenger');
                $flashMessenger->addWarningMessage('Access denied.');
                return $this->getRedirectionResponse($event);
            }
        }
    }
    /**
     * checks authorization
     *
     * @param MvcEvent $event
     * @param User $user
     * @return boolean true if current is authorized access to current resource
     */
    public function checkAcl(MvcEvent $event, User $user)
    {

        $match = $event->getRouteMatch();
        if (! $match) {
            return;
        }

        $controllerFQCN = $match->getParam('controller');
        $controllerName = substr($controllerFQCN, strrpos($controllerFQCN, '\\') + 1, -10);
        $resource = strtolower((new \Zend\Filter\Word\CamelCaseToDash)->filter($controllerName));
        $privilege = $match->getParam('action');
        $acl = $event->getApplication()->getServiceManager()->get('acl');
        $role = (string)$user->getRole();
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
