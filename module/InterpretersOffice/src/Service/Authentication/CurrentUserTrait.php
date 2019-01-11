<?php
/**
 * module/InterpretersOffice/src/Service/Authentication/CurrentUserTrait.php
 */

namespace InterpretersOffice\Service\Authentication;

use InterpretersOffice\Entity;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

/**
 * fetch current user
 */
trait CurrentUserTrait
{
    /**
     * currently authenticated user
     *
     * @var Entity\User
     */
    protected $user;

    /**
     * gets current user
     *
     * @param  LifecycleEventArgs $args
     * @return Entity\User
     */
    public function getAuthenticatedUser(LifecycleEventArgs $args)
    {
        if (! $this->auth) {            
            return null;
        }
        if (! $this->user) {
            $em = $args->getObjectManager();
            $id = $this->auth->hasIdentity() ? $this->auth->getIdentity()->id :
                null;
            if (! $id) {
                return null;
            }
            $this->user = $em->createQuery(
                'SELECT u FROM InterpretersOffice\Entity\User u WHERE u.id = :id'
            )
                ->setParameters(['id' => $id])
                ->useResultCache(true)->getOneOrNullResult();
        }

        return $this->user;
    }

    /**
     * gets the Person belong to the current User
     *
     * @param  LifecycleEventArgs $args [description]
     * @return Entity\Person
     */
    public function getCurrentUserPerson(LifecycleEventArgs $args)
    {

        if ($this->user) {
            return $this->user->getPerson();
        }
        $em = $args->getObjectManager();
        $person_id = $this->auth->getIdentity()->person_id;
        $dql = 'SELECT p FROM InterpretersOffice\Entity\Person p WHERE p.id = :person_id';
        $person = $em->createQuery($dql)->setParameters([':person_id' => $person_id])
            ->useResultCache(true)->getOneOrNullResult();

        return $person;
    }
}
