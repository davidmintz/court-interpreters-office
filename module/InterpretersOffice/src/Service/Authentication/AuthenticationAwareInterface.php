<?php

/** module/InterpretersOffice/src/Service/AuthenticationAwareInterface.php */

namespace InterpretersOffice\Service\Authentication;

use Zend\Authentication\AuthenticationService;

/**
 * for factories to know when to inject the auth service
 */
interface AuthenticationAwareInterface
{

	public function setAuthenticationService(AuthenticationService $auth);

}
