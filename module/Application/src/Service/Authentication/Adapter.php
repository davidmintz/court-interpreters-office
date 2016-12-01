<?php 

namespace Application\Service\Authentication;


use DoctrineModule\Authentication\Adapter\ObjectRepository;
use Application\Service\Authentication\Result;
use Application\Entity\User;

class Adapter extends ObjectRepository
{

    public function __construct($options = [])
    {
        //echo "\ncallable option passed? ";
        // var_dump(isset($options['credential_callable']));
        parent::__construct($options);
    }

   public function authenticate()
    {
        $this->setup();
        $options  = $this->options;
        $objectManager = $options->getObjectManager();
        $query = $objectManager->createQuery("SELECT u FROM Application\Entity\User u JOIN u.person p "
                . "WHERE p.email = :identity OR u.username = :identity")
                ->setParameters([':identity'=>$this->identity]);
        
        $identity = $query->getOneOrNullResult();
            // rather than:
            //->getObjectRepository()->findOneBy(array($options->getIdentityProperty() => $this->identity));

        if (!$identity) {            
            $this->authenticationResultInfo['code']       = \Zend\Authentication\Result::FAILURE_IDENTITY_NOT_FOUND;
            $this->authenticationResultInfo['messages'][] = 'A record with the supplied identity could not be found.';

            return $this->createAuthenticationResult();
        }
        return $this->validateIdentity($identity);

    }
    
    /**
     * validates the identity
     * @param User - not type-hinted for the sake of interface compatibility
     * @return \Application\Service\Authentication\Result
     */
    protected function validateIdentity($identity) {

        //parent::validateIdentity($identity);
        $credentialProperty = $this->options->getCredentialProperty();
        $getter             = 'get' . ucfirst($credentialProperty);
        $documentCredential = null;

        if (method_exists($identity, $getter)) {
            $documentCredential = $identity->$getter();
        } elseif (property_exists($identity, $credentialProperty)) {
            $documentCredential = $identity->{$credentialProperty};
        } else {
            throw new Exception\UnexpectedValueException(
                sprintf(
                    'Property (%s) in (%s) is not accessible. You should implement %s::%s()',
                    $credentialProperty,
                    get_class($identity),
                    get_class($identity),
                    $getter
                )
            );
        }
        //$identity->getActive() ? " user is active ": " user is NOT active ";
        $credentialValue = $this->credential;
        $callable        = $this->options->getCredentialCallable();
        //var_dump($callable);
        if ($callable) {
            $credentialValue = call_user_func($callable, $identity, $credentialValue);
        } 

        if ($credentialValue !== true && $credentialValue !== $documentCredential) {
            $this->authenticationResultInfo['code']       = Result::FAILURE_CREDENTIAL_INVALID;
            $this->authenticationResultInfo['messages'][] = 'Supplied credential is invalid.';

            return $this->createAuthenticationResult();
        }
        // this is in addition to the method we've overridden
        if (! $identity->isActive()) {
             $this->authenticationResultInfo['code']       = Result::FAILURE_USER_ACCOUNT_DISABLED;
             $this->authenticationResultInfo['messages'][] = 'User account is disabled (inactive).';
             
             return $this->createAuthenticationResult();
        }

        $this->authenticationResultInfo['code']       = Result::SUCCESS;
        $this->authenticationResultInfo['identity']   = $identity;
        $this->authenticationResultInfo['messages'][] = 'Authentication successful.';

        return $this->createAuthenticationResult();
    }

     /**
     * Creates a Application\Service\Authentication\Result object from the information 
     * that has been collected during the authenticate() attempt.
     *
     * @return Application\Service\Authentication\Result
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