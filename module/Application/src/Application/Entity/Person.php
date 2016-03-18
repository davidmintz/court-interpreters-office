<?php 

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity  @ORM\Table(name="people") 
 *
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"person" = "Person", "judge" = "Judge", "user"="User", "interpreter"="Interpreter"})
 */
// Columns that have NOT NULL constraints have to be on the root entity of the single-table inheritance hierarchy.

class Person 
{

	/**
	 * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
	 */
	protected $id;

    /**
     * @ORM\Column(type="string",length=50,nullable=true)
     * @var string
     */
    protected $email;

    /**
     * @ORM\Column(type="string",length=50,nullable=false)
     * @var string
     */
    protected $lastname;
	

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
     * Set email
     *
     * @param string $email
     *
     * @return Person
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return Person
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }
}
