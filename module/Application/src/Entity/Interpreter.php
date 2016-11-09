<?php

/** module/Application/src/Entity/Interpreter.php */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity representing an Interpreter.
 *
 * @ORM\Entity
 * @ORM\Table(name="interpreters")
 */
class Interpreter extends Person
{
    /**
     * entity id.
     *
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
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
     * @ORM\Column(type="date")
     *
     * @var string
     */
    protected $dob;

    /**
     * working languages.
     *
     * @ORM\OneToMany(targetEntity="InterpreterLanguage",mappedBy="interpreter", cascade={"persist", "remove"})
     * ORM\JoinColumn(onDelete="CASCADE")
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
        $this->interpreterLanguages[] = $interpreterLanguage;

        return $this;
    }

    /**
     * Remove interpreterLanguage.
     *
     * @param \Application\Entity\InterpreterLanguage $interpreterLanguage
     *
     * @return Interpreter
     */
    public function removeInterpreterLanguage(InterpreterLanguage $interpreterLanguage)
    {
        $this->interpreterLanguages->removeElement($interpreterLanguage);

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
}
