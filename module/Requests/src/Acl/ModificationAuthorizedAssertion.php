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
    public function assert(Acl $acl, RoleInterface $user = null,
        ResourceInterface $controller = null, $privilege = null)
    {
        // test ownership
        if (! $this->AssertOwnership($acl, $user, $controller, $privilege)) {
            return false;
        }
        // test timeliness
        if (! $this->assertTimeliness($acl, $user, $controller, $privilege)) {
            return false;
        }

        return true;
    }

    /**
     * asserts user is authorized to modify request
     *
     * @param  Acl    $acl
     * @param  RoleInterface $user
     * @param  ResourceInterface $controller
     * @param  string $privilege
     * @return boolean
     */
    public function AssertOwnership(Acl $acl, RoleInterface $user = null,
        ResourceInterface $controller = null, $privilege = null)
    {
        $hat = $user->getPerson()->getHat();
        $request  = $controller->getEntity();
        $identity = $controller->getIdentity();
        if ($hat->isJudgesStaff()) {
            // the Judge property of this Request entity
            // has to be among the User's judges
            $judge_ids = $identity->judge_ids;
            $request_judge_id = $request->getJudge()->getId();
            return in_array($request_judge_id,$judge_ids);
        } else {
            // the current User has to be the User who created the entity
            $created_by = $request->getSubmitter();
            return $identity->person_id == $created_by->getId();
        }
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
    public function assertTimeliness(Acl $acl, RoleInterface $user = null,
        ResourceInterface $controller = null, $privilege = null)
    {

        $now = new \DateTime();
        $deadline = $controller->getTwoBusinessDaysFromDate($now);
        $request = $controller->getEntity();
        $request_date = new \DateTime(
            "{$request->getDate()->format('Y-m-d')} {$request->getTime()->format('H:i')}"
        );
        echo __FUNCTION__ , " returning ...";
        return $request_date > $deadline;
    }

}
