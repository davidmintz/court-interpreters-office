<?php 

namespace InterpretersOffice\Service\Authentication;


use DoctrineModule\Authentication\Adapter\ObjectRepository;
use InterpretersOffice\Service\Authentication\Result;
use InterpretersOffice\Entity\User;

class Adapter extends ObjectRepository
{

    public function __construct($options = [])
    {
        parent::__construct($options);
        // maybe we will do something here
    }

   public function authenticate()
    {
        $this->setup();
        $options  = $this->options;
        $objectManager = $options->getObjectManager();
        $query = $objectManager->createQuery("SELECT u FROM InterpretersOffice\Entity\User u JOIN u.person p "
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
     * @param User $identity -- not type-hinted for the sake of interface compatibility
     * @return \InterpretersOffice\Service\Authentication\Result
     */
    protected function validateIdentity($identity) {

        if (! method_exists($identity, 'getPassword')) {
            throw new Exception\UnexpectedValueException(
                  'validateIdentity() expects an object that implements '
                    . ' a public getPassword() method'
            );
        }
        $documentCredential = $identity->getPassword();
        
        // $documentCredential means the hashed password, as stored
        $credentialValue = $this->credential; // i.e., submitted password
        $callable        = $this->options->getCredentialCallable();
        if ($callable) {
            $credentialValue = call_user_func($callable, $identity, $credentialValue);
        } 
        if ($credentialValue !== true && $credentialValue !== $documentCredential) {
            $this->authenticationResultInfo['code']       = Result::FAILURE_CREDENTIAL_INVALID;
            $this->authenticationResultInfo['messages'][] = 'Supplied credential is invalid.';

            return $this->createAuthenticationResult();
        }
        // this is what we've added to the parent method
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