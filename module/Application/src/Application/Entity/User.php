<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity  @ORM\Table(name="users") */

class User // extends Person // no more
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


    /* to be continued */

    /**
     * Set password
     *
     * @param string $password
     *
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
     * @param \Application\Entity\Person $person
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
     * @return \Application\Entity\Person
     */
    public function getPerson()
    {
        return $this->person;
    }
}
