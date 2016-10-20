<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/** 
 * Entity representing a user of the application.
 * 
 * A user "has a" account owner, which is a Person, so User composes a Person. 
 * Inheritance is not the best option because there will be cases where an 
 * Interpreter and a User will both point to the same Person, and Doctrine 
 * doesn't have an inheritance strategy that works well with that. 
 * 
 * @see http://stackoverflow.com/questions/37306930/doctrine-inheritance-strategy-when-two-different-subclasses-extend-the-same-enti
 * 
 * @ORM\Entity  
 * @ORM\Table(name="users") 
 */

class User
{

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     * 
     * @var int
     */
    protected $id;

    /**
    * @ORM\OneToOne(targetEntity="Person",fetch="EAGER")
    * @ORM\JoinColumn(nullable=false)
    */
    protected $person;

    /**
    * @ORM\Column(type="string",length=255,options={"nullable":false})
    * @var string
    */
    protected $password;

    
    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="Role")
     * @var Role
     */
    protected $role;
    
    /* to be continued */

    /**
     * Set password
     *
     * @param string $password
     * @todo hash it
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set person
     *
     * @param Person $person
     *
     * @return User
     */
    public function setPerson(\Application\Entity\Person $person = null)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * Get person
     *
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }
    /**
     * get role
     * 
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }
    /**
     * set role
     * 
     * @param Role $role
     * @return User
     */
    public function setRole(Role $role)
    {
        $this->role = $role;
        return $this;
    }
}
