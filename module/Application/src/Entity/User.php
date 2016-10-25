<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


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
 * @ORM\HasLifecycleCallbacks
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
     *
     * @var string
     */
    protected $password;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="Role")
     *
     * @var Role
     */
    protected $role;

    /**
     * @ORM\Column(type="boolean",options={"nullable":false,"default":false})
     *
     * @var boolean true if account is active (enabled)
     */
    
    protected $active;


    /**
     * Judge(s) to whom a user of hat Law Clerk or Courtroom Deputy is assigned.
     *
     * Most of these users have one and only one judge, but there can be cases where 
     * they have more than one, hence the many-to-many rather than many-to-one. This 
     * is unidirectional.
     * @todo consider whether/how to solve the efficiency/aesthetic problem that a 
     * lot of Users have NO judges, and therefore do not need this at all. Subclass?
     *
     * @ORM\ManyToMany(targetEntity="Judge")
     * @ORM\JoinTable(name="clerks_judges",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="judge_id", referencedColumnName="id")}
     * )
     * @var ArrayCollection
     */
     protected $judges;

     /**
     * constructor
     *
     */
     public function __construct()
     {

        $this->judges = new ArrayCollection();
     }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = password_hash($password,PASSWORD_DEFAULT);

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set person.
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
     * Get person.
     *
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }
    /**
     * get role.
     *
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }
    /**
     * set role.
     *
     * @param Role $role
     *
     * @return User
     */
    public function setRole(Role $role)
    {
        $this->role = $role;

        return $this;
    }


    public function getActive()
    {   
        return $this->active;
    }

    public function setActive($is_active)
    {
        $this->active = $is_active;
        return $this;
    }

    /**
     * Add judge
     *
     * @param \Requests\Entity\Judge $judge
     *
     * @return User
     */
    public function addJudge(Judge $judge) {
        $this->judges[] = $judge;

        return $this;
    }

    /**
     * Remove judge
     *
     * @param Application\Entity\Judge $judge
     */
    public function removeJudge(Judge $judge) {
        $this->judges->removeElement($judge);
    }

    /**
     * Get judges
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getJudges() {
        return $this->judges;
    }

    /**
     * Lifecycle callback to ensure User has an email address. 
     * 
     * This will also be enforced at the form validation level, but we do
     * this for redundancy's sake.
     *  
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function onSave()
    {
       if ($this->getPerson()->getEmail() === NULL) {
           throw new \RuntimeException(
              'A user entity\'s related Person\'s email property cannot be null'
           );
       }
    }
    

}
