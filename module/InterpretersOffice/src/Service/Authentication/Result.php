<?php

/** module/InterpretersOffice/src/Service/Authentication/Result.php */

namespace InterpretersOffice\Service\Authentication;

use Laminas\Authentication\Result as AuthResult;
use InterpretersOffice\Entity\User;

/**
 * extension of Laminas\Authentication\Result just to provide another
 * constant/failure reason.
 *
 * @see \Laminas\Authentication\Result
 */
class Result extends AuthResult
{
    /**
     * Failure due to account inactive.
     */
    const FAILURE_USER_ACCOUNT_DISABLED = -10;

    /**
     * @var $entity User
     */
    protected $entity;

    /**
     * gets the User entity
     * 
     * @return User 
     */
    public function getUserEntity()
    {
        return $this->entity;
    }

    /**
     * gets User entity
     * 
     * @param User $entity
     * @return Result
     */
    public function setUserEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }
}
