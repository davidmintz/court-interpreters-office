<?php
/**
 * module/InterpretersOffice/src/Service/Authentication/Adapter.php.
 */

namespace InterpretersOffice\Service\Authentication;

use InterpretersOffice\Entity\User;
use Doctrine\ORM\EntityManager;
use Laminas\Authentication\Adapter\AbstractAdapter;

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
            "SELECT u, p, r, h, j FROM InterpretersOffice\Entity\User u JOIN u.person p "
                . " JOIN p.hat h LEFT JOIN u.judges j JOIN u.role r "
            .'WHERE p.email = :identity OR u.username = :identity'
        )
           ->setParameters([':identity' => $this->identity]);

        $user = $query->getOneOrNullResult();

        if (! $user) {
            $this->authenticationResultInfo['code'] = \Laminas\Authentication\Result::FAILURE_IDENTITY_NOT_FOUND;
            $this->authenticationResultInfo['messages'][] = 'A record with the supplied identity could not be found.';

            return $this->createAuthenticationResult();
        }

        return $this->validateIdentity($user);
    }

    /**
     * validates the identity.
     *
     * @param User $identity
     *
     * @return Result
     */
    protected function validateIdentity(User $user) : Result
    {
        if (! method_exists($user, 'getPassword')) {
            throw new \Exception(
                'validateIdentity() expects an object that implements '
                    .' a public getPassword() method'
            );
        }
        $this->authenticationResultInfo['identity'] = $user;
        $hash = $user->getPassword();
        $password = $this->credential;
        $valid = password_verify($password, $hash);

        if (! $valid) {
            $this->authenticationResultInfo['code'] = Result::FAILURE_CREDENTIAL_INVALID;
            $this->authenticationResultInfo['messages'][] = 'Supplied credential is invalid.';

            return $this->createAuthenticationResult();
        }

        if (! $user->isActive()) {
            $this->authenticationResultInfo['code'] = Result::FAILURE_USER_ACCOUNT_DISABLED;
            $this->authenticationResultInfo['messages'][] = 'User account is disabled (inactive).';

            return $this->createAuthenticationResult();
        }

        $this->authenticationResultInfo['code'] = Result::SUCCESS;
        // $this->authenticationResultInfo['identity'] = $user;
        $this->authenticationResultInfo['messages'][] = 'Authentication successful.';

        return $this->createAuthenticationResult();
    }

    /**
     * Creates a InterpretersOffice\Service\Authentication\Result object from
     * the information collected by authenticate().
     *
     * @return InterpretersOffice\Service\Authentication\Result
     */
    protected function createAuthenticationResult() : Result
    {
        if (! isset($this->authenticationResultInfo['identity'])) {
            return new Result(
                $this->authenticationResultInfo['code'],
                null,
                $this->authenticationResultInfo['messages']
            );
        }
        /** @var \InterpretersOffice\Entity\User $entity */
        $entity = $this->authenticationResultInfo['identity'];
        $user = new \stdClass();
        $judges = $entity->getJudges();
        $user->judge_ids = [];
        if ($judges) {
            foreach ($judges as $judge) {
                $user->judge_ids[] = $judge->getId();
            }
        }
        $person = $entity->getPerson();
        $hat = $person->getHat();
        $user->lastname = $person->getLastname();
        $user->firstname = $person->getFirstname();
        $user->person_id = $person->getId();
        $user->email = $person->getEmail();
        $user->hat = (string)$hat;
        $user->is_judge_staff = $hat->getIsJudgeStaff();
        $user->username = $entity->getUserName();
        $user->role = (string)$entity->getRole();
        $user->id = $entity->getId();


        $result = new Result(
            $this->authenticationResultInfo['code'],
            $user,
            $this->authenticationResultInfo['messages']
        );
        return $result->setUserEntity($entity);


    }
}
