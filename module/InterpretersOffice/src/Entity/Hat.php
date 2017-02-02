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
 * @ORM\Entity(repositoryClass="InterpretersOffice\Entity\Repository\HatRepository")
 * @ORM\Table(name="hats",uniqueConstraints={@ORM\UniqueConstraint(name="hat_idx",columns={"name"})})
 */
class Hat
{
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
     * @ORM\Column(type="boolean",nullable=false,name="can_be_anonymous",options={"default":false})
     *
     * @var bool true if this Hat does not have to be identified
     */
    protected $anonymous = false;

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
     * get anonymous.
     *
     * @return bool
     */
    public function getAnonymous()
    {
        return $this->anonymous;
    }

    /**
     * set anonymous property.
     *
     * @param bool $flag
     *
     * @return Hat
     */
    public function setAnonymous($flag)
    {
        $this->anonymous = $flag;

        return $this;
    }

    /**
     * proxies to getAnonymous().
     */
    public function anonymous()
    {
        return $this->getAnonymous();
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
}
