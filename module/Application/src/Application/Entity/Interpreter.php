<?php 

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity  @ORM\Table(name="interpreters") 
 */
// Columns that have NOT NULL constraints have to be on the root entity of the single-table inheritance hierarchy.

class Interpreter 
{

	/**
	 * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
	 */
	protected $id;

    /**
     * @ORM\Column(type="string",length=16,nullable=true)
     * @var string
     */
    protected $phone;

    /**
     * @ORM\Column(type="date")
     * @var string
     */
    protected $dob;
	
    /**
     * 
     * @ORM\OneToOne(targetEntity="Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id",onDelete="CASCADE",nullable=false)
     * 
     * @var Person
     */
    protected $person;

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Interpreter
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set dob
     *
     * @param \DateTime $dob
     *
     * @return Interpreter
     */
    public function setDob($dob)
    {
        $this->dob = $dob;

        return $this;
    }

    /**
     * Get dob
     *
     * @return \DateTime
     */
    public function getDob()
    {
        return $this->dob;
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
     * @return Interpreter
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
