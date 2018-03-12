<?php /** module/InterpretersOffice/src/Service/Authentication/CurrentUserTrait.php */

namespace InterpretersOffice\Service\Authentication;

use Zend\Authentication\AuthenticationServiceInterface;
use Doctrine\ORM\EntityManagerInterface as EntityManager;

/**
 * gets the currently authenticated user
 *
 */
trait CurrentUserTrait
{
    /*
     * auth
     *
     * @var AuthenticationServiceInterface
     */
    //protected $auth;

    /**
     * gets the User entity corresponding to authenticated identity
     *
     * @param EntityManager $em
     * @return Entity\User
     */
    protected function getAuthenticatedUser(EntityManager $em)
    {
        $dql = 'SELECT u FROM InterpretersOffice\Entity\User u WHERE u.id = :id';
        $id = $this->auth->getIdentity()->id;
        $query = $em->createQuery($dql)
                ->setParameters(['id' => $id])
                ->useResultCache(true);
        $user = $query->getOneOrNullResult();

        return $user;
    }
}
