<?php

/** module/Application/src/Entity/Role.php */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity representing a Role in the authorization system.
 *
 * Every User has one and only one Role. The Roles are hard-coded in the
 * 'roles' database table: administrator, manager, and submitter. A submitter
 * can read/write requests for interpreting services. An administrator can do
 * everything except read/write submitter requests. A manager can do everything
 * an administrator can do except manage other users in the manager and
 * administrator roles.
 *
 * @ORM\Entity
 * @ORM\Table(name="roles")
 */
class Role
{
    /**
     * entity id.
     *
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    protected $id;

    /**
     * name of the role.
     *
     * @ORM\Column(type="string",length=40,nullable=false)
     *
     * @var string
     */
    protected $name;

    /**
     * returns string representation of Role entity.
     *
     * @return string
     */
    public function __toString()
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
     * @todo consider if this method should even exist,
     * since roles are going to be hard-coded at installation
     * and will not change thereafter
     *
     * @param string $name
     *
     * @return Role
     */
    public function setName($name)
    {
        $this->name = $name;

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
}
