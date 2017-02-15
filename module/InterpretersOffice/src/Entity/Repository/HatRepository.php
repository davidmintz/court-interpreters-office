<?php
/**
 *  module/InterpretersOffice/src/Entity/Repository/HatRepository.php.
 */

namespace InterpretersOffice\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Hat repository.
 *
 * @author david
 */
class HatRepository extends EntityRepository
{
    use ResultCachingQueryTrait;
    
    /**
     * returns Hat entities for Person form's select element.
     *
     * excludes the Hat entities that correspond to subtypes of the
     * Person entity -- Judge and Interpreter -- whose hat-types are constrained
     * to certain values.
     *
     * @return array
     */
    public function getHatsForPersonForm()
    {
        // this returns "AUSA", "defense attorney", etc as well as 
        // the usually-anonymous "Magistrate" and "Pretrial" unless
        // we decide to do otherwise
        $dql = 'SELECT h FROM InterpretersOffice\Entity\Hat h '
           .' WHERE h.name NOT LIKE \'%court interpreter%\' AND h.name <> \'Judge\''
           .' AND h.role IS NULL';
        $query = $this->createQuery($dql);

        return $query->getResult();
    }   
    
     /**
     * figures out how to populate Hat select menu based on the action and the 
     * role of the current user, to comply with ACL rules
      * 
     * @param string $auth_user_role role of currently authenticated user
     * @param string $action current action: either 'create' or 'update'
     * @return array
     */
    public function getHatsForUserForm($auth_user_role,$action)
    {
        echo "hello? $auth_user_role, $action...";        
         if (! in_array($action,['create','update'])) {
            throw new \DomainException('invalid action parameter');
        }
        $dql = 'SELECT h FROM InterpretersOffice\Entity\Hat h JOIN h.role r ';
        switch ($auth_user_role){
        
            case 'anonymous': // e.g., user registration                
            case 'submitter': // normally they cannot update, but...
                $dql .= 'WHERE r.name = \'submitter\'';
            break;
            case 'manager': 
                if ($action == 'update') {
                    $dql .= 'WHERE r.name <> \'submitter\'';                           
                } else {
                    throw new \RuntimeException("$action on user entity denied to $auth_user_role");
                }
            break;

            case 'administrator': 
               // nothing more to do
            break;
            default :
                 throw new \RuntimeException(sprintf('%s: %s denied to %s',
                         __METHOD__,$action,$auth_user_role));
        }
        $dql .= ' ORDER BY h.name';                 
        $query = $this->createQuery($dql);

        return $query->getResult();    
        
    }
    
    /**
     * get Hats for InterpreterFieldset.
     *
     * @return array
     */
    public function getInterpreterHats()
    {
        $query = $this->createQuery(
            'SELECT h  FROM InterpretersOffice\Entity\Hat h WHERE h.name LIKE :what ORDER BY h.name'
        )->setParameters([':what' => '%court interpreter']);

        return $query->getResult();
    }
}
