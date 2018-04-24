<?php
/**
 * module/InterpretersOffice/src/Entity/User.php.
 */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Zend\Permissions\Acl\Resource\ResourceInterface;

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
 * @ORM\Table(name="users",uniqueConstraints={@ORM\UniqueConstraint(name="uniq_person_role", columns={"person_id", "role_id"})})
 * @ORM\Entity(repositoryClass="InterpretersOffice\Entity\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks
 */

class User implements ResourceInterface
{
    /**
     * user id.
     *
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     *
     * @var int
     */
    protected $id;

    /**
     * Person who owns User account.
     *
     * @ORM\OneToOne(targetEntity="Person",fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $person;

    /**
     * user password.
     *
     * @ORM\Column(type="string",length=255,options={"nullable":false})
     *
     * @var string
     */
    protected $password;

    /**
     * username.
     *
     * @ORM\Column(type="string",length=50,nullable=true,options={"nullable":true})
     *
     * @var string
     */
    protected $username;

    /**
     * The user's role.
     *
     *
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="Role",fetch="EAGER")
     *
     * @see InterpretersOffice\Entity\Role
     *
     * @var Role
     */
    protected $role;

    /**
     * whether account is active or not (i.e., disabled).
     *
     * @ORM\Column(type="boolean",options={"nullable":false,"default":false})
     *
     * @var bool true if account is active (enabled)
     */
    protected $active;

    /**
     * last login timestamp
     *
     * @ORM\Column(type="datetime",name="last_login",options={"nullable":true})
     *
     * @var \DateTime
     */
    protected $lastLogin;

    /**
     * Judge(s) to whom a user of hat Law Clerk or Courtroom Deputy is assigned.
     *
     * Most of these users have one and only one judge, but there can be cases where
     * they have more than one, hence the many-to-many rather than many-to-one. This
     * is unidirectional.
     *
     * @todo consider whether/how to solve the efficiency/aesthetic problem that a
     * lot of Users have NO judges, and therefore do not need this at all. Subclass?
     *
     * @ORM\ManyToMany(targetEntity="Judge")
     * @ORM\JoinTable(name="clerks_judges",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="judge_id", referencedColumnName="id")}
     * )
     *
     * @var ArrayCollection
     */
    protected $judges;

    /**
     * account creation date
     *
     * @ORM\Column(type="datetime")//,options={"nullable":false}
     *
     * @var \DateTime
     */
    protected $created;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->judges = new ArrayCollection();
    }

    /**
     * implements ResourceInterface
     *
     * @return string
     */

    public function getResourceId()
    {
        return $this->getRole()->getName();
    }
    /**
     * verifies password.
     *
     * @param User   $user
     * @param string $submitted_password
     * @return boolean
     */
    public static function verifyPassword(User $user, $submitted_password)
    {

        return password_verify($submitted_password, $user->getPassword());
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
        $this->password = password_hash($password, PASSWORD_DEFAULT);

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
     * Set password.
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
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
    public function setPerson(\InterpretersOffice\Entity\Person $person = null)
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

    /**
     * gets "active" property.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * sets the "active" property for this user account.
     *
     * @param bool $is_active
     *
     * @return User
     */
    public function setActive($is_active)
    {
        $this->active = $is_active;

        return $this;
    }

    /**
     * Add judge.
     *
     * @param \Requests\Entity\Judge $judge
     *
     * @return User
     */
    public function addJudge(Judge $judge)
    {
        $this->judges[] = $judge;

        return $this;
    }

    /**
     * Remove judge.
     *
     * @param InterpretersOffice\Entity\Judge $judge
     */
    public function removeJudge(Judge $judge)
    {
        $this->judges->removeElement($judge);
    }

    /**
     * Get judges.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getJudges()
    {
        return $this->judges;
    }
    /**
     * de facto alias for getActive().
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }
    /**
     * sets last login
     *
     * @param \DateTime $datetime
     * @return \InterpretersOffice\Entity\User
     */
    public function setLastLogin(\DateTime $datetime)
    {
        $this->lastLogin = $datetime;

        return $this;
    }
    /**
     * gets last login
     *
     * @return \Datetime
     */
    public function getLastLogin()
    {
         return $this->lastLogin;
    }

    /**
     * Lifecycle callback to ensure User has an email address.
     *
     * This will also be enforced at the form validation level, but we do
     * this for redundancy's sake.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     *
     * @throws \RuntimeException
     */
    public function onSave()
    {
        if ($this->getPerson()->getEmail() === null) {
            throw new \RuntimeException(
                'A user entity\'s related Person\'s email property cannot be null'
            );
        }
    }

    /**
     * gets creation date
     *
     * @return \Datetime
     */
    public function getCreated()
    {
         return $this->created;
    }

    /**
     * sets creation date
     *
     * @return User
     */
    public function setCreated(\DateTime $created)
    {
         $this->created = $created;

         return $this;
    }

}
