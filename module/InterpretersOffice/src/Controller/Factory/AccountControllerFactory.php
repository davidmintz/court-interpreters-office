<?php

/** module/InterpretersOffice/src/Controller/Factory/AccountControllerFactory.php */

namespace InterpretersOffice\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use InterpretersOffice\Controller\AccountController;

use InterpretersOffice\Service\AccountManager;
use InterpretersOffice\Entity\Listener;
use Laminas\EventManager\EventInterface;
use InterpretersOffice\Entity\User;

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
            // initialize listener
            $container->get(Listener\UpdateListener::class)->setAuth($auth);
        }
        $controller = (new AccountController($em, $auth))
            ->setAccountManager($container->get(AccountManager::class));
        /** @var $sharedEvents Laminas\EventManager\SharedEventManagerInterface */
        $sharedEvents = $container->get('SharedEventManager');
        $log = $container->get('log');
        $sharedEvents->attach(
            $requestedName,
            AccountManager::EVENT_REGISTRATION_SUBMITTED,
            function (EventInterface $event) use ($log) {
                $user = $event->getParam('user');
                $person = $user->getPerson();
                $log->info(
                    sprintf(
                        "new user registration submitted by %s %s, %s",
                        $person->getFirstname(),
                        $person->getLastname(),
                        $person->getEmail()
                    ),[
                        'channel' => 'users',
                        'entity_class' => User::class,
                        'entity_id'    => $user->getId(),
                    ]
                );
            }
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
            $requestedName,
            AccountManager::USER_ACCOUNT_MODIFIED,
            function (EventInterface $event) use ($log)
            {
                $account_updated = $judges_updated = false;
                $before = $event->getParam('before');
                $after = $event->getParam('after');
                $entity = $event->getParam('user');
                if (array_diff($before->judge_ids,$after->judge_ids)
                    or array_diff($after->judge_ids,$before->judge_ids)) {
                    $judges_updated = true;
                }
                foreach (array_keys(get_object_vars($after)) as $prop) {
                    if ('judge_ids' == $prop) {
                        continue;
                    }
                    if ($before->$prop != $after->$prop) {
                        $account_updated = true;
                        $log->debug($before->$prop . ' != ' . $after->$prop);
                        break;
                    }
                }
                if (! $account_updated) {
                    $person_before = $event->getParam('person_before');
                    $person_after = [
                        'mobile'=>$entity->getPerson()->getMobilePhone(),
                        'office'=>$entity->getPerson()->getOfficePhone()
                    ];
                    if ($person_before != $person_after) {
                        $account_updated = true;
                    }
                }
                $username = $entity->getUsername();
                if (! $account_updated and ! $judges_updated) {
                    $log->debug(sprintf(
                        'user %s saved her/his profile without modification',
                        $username
                    ));
                    return;
                }
                if ($account_updated && ! $judges_updated) {
                    $did_what = 'updated her/his account profile';
                } elseif($judges_updated && !$account_updated) {
                    $did_what = 'updated her/his judges';
                } else {
                    $did_what = 'updated her/his account profile, including judges';
                }
                $log->info(
                    "user $username $did_what",
                    [   'entity_class' => get_class($entity),
                        'entity_id'=>$entity->getId(),
                        'account_updated' => $account_updated,
                        'judges_updated' => $judges_updated,
                    ]
                );
            }
        );

        return $controller;
    }
}
