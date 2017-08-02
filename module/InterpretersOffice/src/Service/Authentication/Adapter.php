<?php
/**
 * module/InterpretersOffice/src/Service/Authentication/Adapter.php.
 */

namespace InterpretersOffice\Service\Authentication;

use InterpretersOffice\Entity\User;
use Doctrine\ORM\EntityManager;
use Zend\Authentication\Adapter\AbstractAdapter;

/**
 * Authentication adapter.
 *
 * Our own adapter is necessary because of the entities' structure and
 * relationship. A simple query of a users table is not sufficient because
 * we want to allow users to provide either email or username. The User has
 * a one-to-one relationship with the Person entity, and the email is a property
 * of Person while the username is a property of User. We also return our own
 * subclass of Result that includes a constant meaning account not active
 * (i.e., disabled).
 *
 */
class Adapter extends AbstractAdapter
{
    /**
     * Doctrine entity manager
     * 
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * authenticates the user.
     *
     * @return Result
     */
    public function authenticate()
    {
       
        $objectManager = $this->entityManager;
        $query = $objectManager->createQuery(
            "SELECT u FROM InterpretersOffice\Entity\User u JOIN u.person p"
                . " JOIN u.role r "
            .'WHERE p.email = :identity OR u.username = :identity'
        )
           ->setParameters([':identity' => $this->identity]);

        $identity = $query->getOneOrNullResult();
         
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
     * @param User $identity
     *
     * @return \InterpretersOffice\Service\Authentication\Result
     */
    protected function validateIdentity(User $identity)
    {
        if (! method_exists($identity, 'getPassword')) {
            throw new \Exception(
                'validateIdentity() expects an object that implements '
                    .' a public getPassword() method'
            );
        }
        $hash = $identity->getPassword();
        $password = $this->credential;
        $valid = password_verify($password, $hash);
        
        if (! $valid) {
            $this->authenticationResultInfo['code'] = Result::FAILURE_CREDENTIAL_INVALID;
            $this->authenticationResultInfo['messages'][] = 'Supplied credential is invalid.';

            return $this->createAuthenticationResult();
        }
   
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
        if (! isset($this->authenticationResultInfo['identity'])) {
            return new Result($this->authenticationResultInfo['code'],null, $this->authenticationResultInfo['messages']);
        }
        
        $entity = $this->authenticationResultInfo['identity'];
        $user_object = new \stdClass();
        $person = $entity->getPerson();
        $user_object->lastname = $person->getLastname();
        $user_object->firstname = $person->getFirstname();
        $user_object->email = $person->getEmail();
        $user_object->hat = (string)$person->getHat();
        $user_object->username = $entity->getUserName();
        $user_object->role = (string)$entity->getRole();
        
        
        return new Result(
            $this->authenticationResultInfo['code'],
            $user_object,
            $this->authenticationResultInfo['messages']
        );
    }
}
