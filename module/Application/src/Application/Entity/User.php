<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity  @ORM\Table(name="users") */

class User extends Person 
{

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
     * Set hat
     *
     * @param \Application\Entity\Hat $hat
     *
     * @return User
     */
    public function setHat(\Application\Entity\Hat $hat)
    {
        $this->hat = $hat;

        return $this;
    }

    /**
     * Get hat
     *
     * @return \Application\Entity\Hat
     */
    public function getHat()
    {
        return $this->hat;
    }
}
