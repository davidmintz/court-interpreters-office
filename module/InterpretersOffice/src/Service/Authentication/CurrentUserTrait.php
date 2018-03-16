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
        if (! $this->user) {
            $em = $args->getObjectManager();
            $id = $this->auth->getIdentity()->id;
            $this->user = $em->createQuery('SELECT u FROM InterpretersOffice\Entity\User u WHERE u.id = :id')
                ->setParameters(['id'=>$id])->useResultCache(true)->getOneOrNullResult();
        }

        return $this->user;
    }
}
