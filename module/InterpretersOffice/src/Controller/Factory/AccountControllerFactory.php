<?php

/** module/InterpretersOffice/src/Controller/Factory/AccountControllerFactory.php */

namespace InterpretersOffice\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use InterpretersOffice\Controller\AccountController;

use InterpretersOffice\Service\AccountManager;
use InterpretersOffice\Entity\Listener;
use InterpretersOffice\Form\User\RegistrationForm;
use InterpretersOffice\Admin\Form\UserForm;

/**
 * Factory class for instantiating AccountController.
 */
class AccountControllerFactory implements FactoryInterface
{
    /**
     * invocation, if you will.
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return AccountController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $auth = $container->get('auth');
        $em = $container->get('entity-manager');
        if ($auth->hasIdentity()) {            
            $container->get(Listener\UpdateListener::class)->setAuth($auth);
        }
        $accountManager = $container->get(AccountManager::class);
        $controller = (new AccountController($em, $auth))
            ->setAccountManager($accountManager);

        // attach event listeners
        /** @var $sharedEvents Laminas\EventManager\SharedEventManagerInterface */
        $sharedEvents = $container->get('SharedEventManager');
        $log = $container->get('log');
        $sharedEvents->attach(
            $requestedName, AccountManager::EVENT_REGISTRATION_SUBMITTED,
            [$accountManager,'onSubmitRegistration']
        );

        $sharedEvents->attach($requestedName,AccountManager::EVENT_EMAIL_VERIFIED,
        function($e) use ($log)
        {
            $user = $e->getParam('user');
            $email = $user->getPerson()->getEmail();
            $log->info("successful email verification by user $email",[
                'entity_class'=> get_class($user),
                'entity_id'   => $user->getId(),
                'channel'     => 'security',
            ]);
        });

        $sharedEvents->attach(
            $requestedName, AccountManager::USER_ACCOUNT_MODIFIED,
            [$accountManager,'onModifyAccount']            
        );

        // figure out what form to inject
        // /** @var Laminas\Router\Http\RouteMatch $route_match */
        // $route_match = $container->get('Application')->getMvcEvent()->getRouteMatch();
        // if (! $route_match) {
        //     return $controller;
        // }
        // $action = $route_match->getParams()['action'];
        // if (in_array($action,['register','validate'])) {
        //     $form = $container->get(RegistrationForm::class);
        // } else if ('edit' == $action) {
        //     $form = $container->get(UserForm::class);
        // }
        // var_dump(get_class($route_match));
        // printf(
        //     '<pre>%s</pre>', print_r($route_match->getParams(),true)
        // );



        return $controller;
    }
}
