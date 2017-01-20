<?php
/** module/InterpretersOffice/src/Entity/Interpreter.php */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Entity representing an Interpreter.
 *
 * @ORM\Entity(repositoryClass="InterpretersOffice\Entity\Repository\InterpreterRepository")
 * @ORM\Table(name="interpreters")
 */
class Interpreter extends Person
{
    /**
     * entity id.
     *
     * @ORM\Id @ORM\GeneratedValue(strategy="AUTO") @ORM\Column(type="smallint",options={"unsigned":true})
     */
    protected $id;

    /**
     * phone number.
     *
     * @ORM\Column(type="string",length=16,nullable=true)
     *
     * @var string
     */
    protected $phone;

    /**
     * date of birth.
     *
     * @ORM\Column(type="date",nullable=true)
     *
     * @var string
     */
    protected $dob;

    /**
     * working languages.
     *
     * @ORM\OneToMany(targetEntity="InterpreterLanguage",mappedBy="interpreter", cascade={"persist", "remove"},orphanRemoval=true)
     * 
     *
     * @var ArrayCollection of InterpreterLanguage
     */
    protected $interpreterLanguages;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->interpreterLanguages = new ArrayCollection();
    }

    /**
     * Set phone.
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
     * Get phone.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set dob.
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
     * Get dob.
     *
     * @return \DateTime
     */
    public function getDob()
    {
        return $this->dob;
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
     * shortcut for addInterpreterLanguage().
     *
     * @param Language $language
     *
     * @return Interpreter
     */
    public function addLanguage(Language $language)
    {
        $this->addInterpreterLanguage(
            new InterpreterLanguage($this, $language)
        );

        return $this;
    }

    /**
     * Add interpreterLanguage.
     *
     * @param InterpreterLanguage $interpreterLanguage
     *
     * @return Interpreter
     */
    public function addInterpreterLanguage(InterpreterLanguage $interpreterLanguage)
    {
        $this->interpreterLanguages->add($interpreterLanguage);
        return $this;
    }

    /**
     * Remove interpreterLanguage.
     *
     * @param \InterpretersOffice\Entity\InterpreterLanguage $interpreterLanguage
     *
     * @return Interpreter
     */
    public function removeInterpreterLanguage(InterpreterLanguage $interpreterLanguage)
    {       
        
        $this->interpreterLanguages->removeElement($interpreterLanguage);
        // not sure whether/when the following is required
        //$interpreterLanguage->setLanguage(null);
        //$interpreterLanguage->setInterpreter(null);
        return $this;
    }

    /**
     * Get interpreterLanguages.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInterpreterLanguages()
    {
        return $this->interpreterLanguages;
    }
    
    /*
    AllowRemove strategy for DoctrineModule hydrator requires both addInterpreterLanguages and 
     removeInterpreterLanguages to be defined in InterpretersOffice\Entity\Interpreter
     entity domain code, but one or both seem to be missing
     */
    
    
    public function addInterpreterLanguages(Collection $interpreterLanguages)
    {
        foreach ($interpreterLanguages as $interpreterLanguage) {
            
            $interpreterLanguage->setInterpreter($this);
            $this->interpreterLanguages->add($interpreterLanguage);
        }
    }

    public function removeInterpreterLanguages(Collection $interpreterLanguages)
    {        
        foreach ($interpreterLanguages as $interpreterLanguage) {            
            $this->interpreterLanguages->removeElement($interpreterLanguage);
        }
    }
                
}
