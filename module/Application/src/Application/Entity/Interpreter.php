<?php 

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity  @ORM\Table(name="interpreters") 
 */

class Interpreter extends Person
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
     * @ORM\OneToMany(targetEntity="InterpreterLanguage",mappedBy="interpreter", cascade={"persist", "remove"})
     * @var InterpreterLanguage[]
     */
    protected $interpreterLanguages;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->interpreterLanguages = new \Doctrine\Common\Collections\ArrayCollection();
    }


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
     * Add interpreterLanguage
     *
     * @param \Application\Entity\InterpreterLanguage $interpreterLanguage
     *
     * @return Interpreter
     */
    public function addInterpreterLanguage(\Application\Entity\InterpreterLanguage $interpreterLanguage)
    {
        $this->interpreterLanguages[] = $interpreterLanguage;

        return $this;
    }

    /**
     * Remove interpreterLanguage
     *
     * @param \Application\Entity\InterpreterLanguage $interpreterLanguage
     */
    public function removeInterpreterLanguage(\Application\Entity\InterpreterLanguage $interpreterLanguage)
    {
        echo "running ".__FUNCTION__." ... ";
        echo count($this->interpreterLanguages). " is our number of languages\n";        
        $this->interpreterLanguages->removeElement($interpreterLanguage);
        echo count($this->interpreterLanguages). " is now our number of languages\n";
    }

    /**
     * Get interpreterLanguages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInterpreterLanguages()
    {
        return $this->interpreterLanguages;
    }
}
