<?php

/**
 * module/InterpretersOffice/src/Service/Authentication/Adapter.php.
 */

namespace InterpretersOffice\Service\Authentication;

use DoctrineModule\Authentication\Adapter\ObjectRepository;
use InterpretersOffice\Entity\User;

/**
 * Authentication adapter.
 *
 * Our own adapter is necessary because of the entities structure and
 * relationship. A simple query of a users table is not sufficient because
 * we want to allow users to provide either email or username. The User has
 * a one-to-one relationship with the Person entity, and the email is a property
 * of Person while the username is a property of User. We also return our own
 * subclass of Result that includes a constant meaning account not active
 * (i.e., disabled).
 *
 * A work in progress. We might not really need to extend the Doctrine adapter
 * or pass in a credential_callable via the constructor -- we might as well
 * hard-code it.
 */
class Adapter extends ObjectRepository
{
    /**
     * constructor.
     *
     * @param array|\DoctrineModule\Options\Authentication $options
     */
    public function __construct($options = [])
    {
        parent::__construct($options);
        // maybe we will do something here down the road.
        // for now this is superfluous
    }

   /**
    * authenticates the user.
    *
    * @return Result
    */
    public function authenticate()
    {
        $this->setup();
        $options = $this->options;
        $objectManager = $options->getObjectManager();
        $query = $objectManager->createQuery(
            "SELECT u FROM InterpretersOffice\Entity\User u JOIN u.person p "
            .'WHERE p.email = :identity OR u.username = :identity'
        )
           ->setParameters([':identity' => $this->identity]);

        $identity = $query->getOneOrNullResult();
            // rather than:
            //->getObjectRepository()->findOneBy(array($options->getIdentityProperty() => $this->identity));

        if (! $identity) {
            $this->authenticationResultInfo['code'] = \Zend\Authentication\Result::FAILURE_IDENTITY_NOT_FOUND;
            $this->authenticationResultInfo['messages'][] = 'A record with the supplied identity could not be found.';

            return $this->createAuthenticationResult();
        }

        return $this->validateIdentity($identity);
    }

    /**
     * validates the identity.
     *
     * @param User $identity -- not type-hinted for the sake of interface compatibility
     *
     * @return \InterpretersOffice\Service\Authentication\Result
     */
    protected function validateIdentity($identity)
    {
        if (! method_exists($identity, 'getPassword')) {
            throw new Exception\UnexpectedValueException(
                'validateIdentity() expects an object that implements '
                    .' a public getPassword() method'
            );
        }
        $documentCredential = $identity->getPassword();

        // $documentCredential means the hashed password, as stored
        $credentialValue = $this->credential; // i.e., submitted password
        $callable = $this->options->getCredentialCallable();
        if ($callable) {
            $credentialValue = call_user_func($callable, $identity, $credentialValue);
        }
        if ($credentialValue !== true && $credentialValue !== $documentCredential) {
            $this->authenticationResultInfo['code'] = Result::FAILURE_CREDENTIAL_INVALID;
            $this->authenticationResultInfo['messages'][] = 'Supplied credential is invalid.';

            return $this->createAuthenticationResult();
        }
        // this is what we've added to the parent method
        if (! $identity->isActive()) {
            $this->authenticationResultInfo['code'] = Result::FAILURE_USER_ACCOUNT_DISABLED;
            $this->authenticationResultInfo['messages'][] = 'User account is disabled (inactive).';

            return $this->createAuthenticationResult();
        }

        $this->authenticationResultInfo['code'] = Result::SUCCESS;
        $this->authenticationResultInfo['identity'] = $identity;
        $this->authenticationResultInfo['messages'][] = 'Authentication successful.';

        return $this->createAuthenticationResult();
    }

    /**
     * Creates a InterpretersOffice\Service\Authentication\Result object from the information
     * that has been collected during the authenticate() attempt.
     *
     * @return InterpretersOffice\Service\Authentication\Result
     */
    protected function createAuthenticationResult()
    {
        return new Result(
            $this->authenticationResultInfo['code'],
            $this->authenticationResultInfo['identity'],
            $this->authenticationResultInfo['messages']
        );
    }
}
