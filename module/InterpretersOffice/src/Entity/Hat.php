<?php

/** module/InterpretersOffice/src/Application/Entity/Hat.php */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity class representing the "hat" (job description, title) a Person wears.
 *
 * Examples: staff interpreter, contract interpreter, AUSA, USPO, defense
 * attorney, etc. These should be set up at installation and rarely if ever
 * changed thereafter.
 *
 * @ORM\Entity(repositoryClass="InterpretersOffice\Entity\Repository\HatRepository",readOnly=true)
 * @ORM\Table(name="hats",uniqueConstraints={@ORM\UniqueConstraint(name="hat_idx",columns={"name"})})
 * @ORM\HasLifecycleCallbacks
 */
class Hat
{

    const ANONYMITY_NEVER = 0;
    const ANONYMITY_ALWAYS = 1;
    const ANONYMITY_OPTIONAL = 2;

    /**
     * entity id.
     *
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    protected $id;

    /**
     * name of the Hat.
     *
     * @ORM\Column(type="string",length=50,nullable=false)
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="integer",nullable=false,name="anonymity",options={"default":0,"unsigned":true})
     *
     * @var int anonymity when submitting: 0 => never, 1 => always, 2 => optional,
     */
    protected $anonymity = 0;

    /**
     * The Role corresponding to this Hat.
     *
     * The Role is relevant to User authorization. For most Hats, it is null.
     *
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumn(nullable=true)
     *
     * @see InterpretersOffice\Entity\Role
     *
     * @var Role
     */
    protected $role;

    /**
     * Is the person "active," or only of historical interest?
     *
     * If false, the entity by default will not be displayed in dropdown menus.
     *
     * @ORM\Column(type="boolean",nullable=false,name="is_judges_staff")
     *
     * @var bool
     */
    protected $isJudgeStaff = false;

    /**
     * Constructor
     *
     * @param string $name name of Hat
     */
    public function __construct($name = null)
    {
        $this->name = $name;
    }

    /**
     * Lifecycle callback to ensure valid state.
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
        if ($this->getRole() && $this->anonymity != 0) {
            throw new \RuntimeException(
                'If a Hat has a Role, its anonymity must be set to 0 ("never")'
            );
        }
        /*
        not so. 'contract court interpreter' has no Role but can't be
            anonymous
        if (! $this->getRole() && $this->anonymity == 0) {
            throw new \RuntimeException( sprintf(
                'Error saving Hat %s: if no Role is set, anonymity cannot be "never"',
                $this->getName()
            ));

        }*/
    }

    /**
     * returns string representation of the entity.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * de facto alias for getName().
     *
     * @return string
     */
    public function getHat()
    {
        return $this->name;
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
     * Set name.
     *
     * @param string $type
     *
     * @return Hat
     */
    public function setName($type)
    {
        $this->name = $type;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * Set the anonymity property.
     *
     * Permitted values are: 0 (never); 1 (always); 2 (optional).
     * Determines whether a Person bearing this Hat may, must or must not
     * be anonymous when requesting an interpreter, hence the input validation
     * rules for the Event form. For example, we may decide "defense attorney"
     * can be unidentified or not, but Courtroom Deputy or US Probation Officer
     * must always be identified (i.e., not anonymous). Any Hat that has a Role
     * is never anonymous, because role implies user implies user account
     * implies a known person, so we are required to identity that person rather
     * than allowing insertion of an inexact duplicate person. Most of your Hats
     * will likely have anonymity settings of either "never" or "optional."
     * "always" implies a category for which you don't at all care who the
     * actual person is who requests the interpreter, only the organizational
     * unit (Hat) onwhose behalf the request is submitted.
     *
     * @param int $anonymity
     *
     * @return Hat
     */
    public function setAnonymity($anonymity)
    {
        if (! in_array($anonymity, [0,1,2])) {
            throw new \RuntimeException(
                'illegal value passed to setAnonymity(): '.$anonymity
            );
        }
        $this->anonymity = $anonymity;

        return $this;
    }

    /**
     * gets anonymity
     *
     * @var int
     */
    public function getAnonymity()
    {
        return $this->anonymity;
    }
    /**
     * returns the Role of this Hat.
     *
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }
    /**
     * set the Role of this Hat.
     *
     * @param Role $role
     *
     * @return Hat
     */
    public function setRole(Role $role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * gets is-judge-staff flag
     * @return boolean
     */
    public function getIsJudgeStaff()
    {
        return $this->isJudgeStaff;
    }

    /**
     * proxies to getIsJudgeStaff()
     *
     * @return boolean
     */
    public function isJudgesStaff()
    {
        return $this->getIsJudgeStaff();
    }

    /**
     * sets is-judge-staff flag
     * @param boolean $is_judges_staff
     * @return Hat
     */
    public function setIsJudgeStaff($is_judges_staff)
    {
        $this->isJudgeStaff = $is_judges_staff;

        return $this;
    }
}
