<?php

/** module/InterpretersOffice/src/Service/Authentication/Result.php */

namespace InterpretersOffice\Service\Authentication;

use Zend\Authentication\Result as AuthResult;

/**
 * extension of Zend\Authentication\Result just to provide another
 * constant/failure reason.
 *
 * @see \Zend\Authentication\Result
 */
class Result extends AuthResult
{
    /**
     * Failure due to account inactive.
     */
    const FAILURE_USER_ACCOUNT_DISABLED = -10;
}
