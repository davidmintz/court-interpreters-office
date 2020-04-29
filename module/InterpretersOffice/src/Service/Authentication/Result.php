<?php

/** module/InterpretersOffice/src/Service/Authentication/Result.php */

namespace InterpretersOffice\Service\Authentication;

use Laminas\Authentication\Result as AuthResult;

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

    protected $entity;

    public function getUserEntity()
    {
        return $this->entity;
    }

    public function setUserEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }
}
