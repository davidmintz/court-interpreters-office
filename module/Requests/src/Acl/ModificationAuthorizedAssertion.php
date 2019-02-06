<?php
/** module/Requests/src/Acl/ModificationAuthorizedAssertion.php */

namespace InterpretersOffice\Requests\Acl;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * Assertion to test if the User is authorized to modify the Request
 */
class ModificationAuthorizedAssertion implements AssertionInterface
{

    /**
     * controller
     *
     * @var AbstractActionController
     */
    private $controller;

    /**
     * Constructor
     *
     * @param AbstractActionController $controller
     *
     */
    public function __construct(AbstractActionController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * implements AssertionInterface
     *
     * @param  Acl    $acl
     * @param  RoleInterface $user
     * @param  ResourceInterface $controller
     * @param  string $privilege
     * @return boolean
     */
    public function assert(
        Acl $acl,
        RoleInterface $user = null,
        ResourceInterface $controller = null,
        $privilege = null
    ) {

        $log = $controller->getEvent()->getApplication()->getServiceManager()->get('log');
        // test ownership
        if (! $this->AssertOwnership($acl, $user, $controller, $privilege)) {
            $log->debug('"ownership" acl assertion returned false!');
            return false;
        }
        // test timeliness
        if (! $this->assertTimeliness($acl, $user, $controller, $privilege)) {
            $log->debug('"timeliness" acl assertion returned false!');
            return false;
        }

        return true;
    }

    /**
     * asserts that user is authorized to modify request
     *
     * @param  Acl    $acl
     * @param  RoleInterface $user
     * @param  ResourceInterface $controller
     * @param  string $privilege
     * @return boolean
     */
    public function assertOwnership(
        Acl $acl,
        RoleInterface $user = null,
        ResourceInterface $controller = null,
        $privilege = null
    ) {
        $hat = $user->getPerson()->getHat();
        $request  = $controller->getEntity();
        $identity = $controller->getIdentity();
        if ($hat->isJudgesStaff()) {
            // the Judge property of this Request entity
            // has to be among the User's judges
            $request_judge_id = $request->getJudge()->getId();
            if (! in_array($request_judge_id, $identity->judge_ids)) {
                return false;
            }
            // it has to be an in-court event
            $category = $request->getEventType()->getCategory();
            if ('in' != $category) {
                return false;
            }
            return true;
        } else {
            // the current User has to be the User who created the entity
            $created_by = $request->getSubmitter();
            return $identity->person_id == $created_by->getId();
        }
    }

    /**
     * asserts that Request datetime is at least two business days from now
     *
     * @param  Acl    $acl
     * @param  RoleInterface $user
     * @param  ResourceInterface $controller
     * @param  string $privilege
     * @return boolean
     */
    public function assertTimeliness(
        Acl $acl,
        RoleInterface $user = null,
        ResourceInterface $controller = null,
        $privilege = null
    ) {

        $now = new \DateTime();
        $deadline = $controller->getTwoBusinessDaysFromDate($now);
        $request = $controller->getEntity();

        $request_date = new \DateTime(
            "{$request->getDate()->format('Y-m-d')} {$request->getTime()->format('H:i')}"
        );
        // $log = $controller->getEvent()->getApplication()
        //     ->getServiceManager()->get('log');
        // $log->debug(sprintf("excuse me? action is $privilege, request %d date is %s, deadline is %s",
        //     $request->getId(),
        //     $request_date->format("Y-m-d H:i"),$deadline->format("Y-m-d H:i")
        // ));
        return $request_date > $deadline;
    }
}
