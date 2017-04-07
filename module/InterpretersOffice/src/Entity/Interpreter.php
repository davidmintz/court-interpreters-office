<?php

/** module/InterpretersOffice/src/Entity/Interpreter.php */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

//
/** @Entity @EntityListeners({"UserListener"}) */

/**
 * Entity representing an Interpreter.
 *
 * @ORM\Entity(repositoryClass="InterpretersOffice\Entity\Repository\InterpreterRepository")
 * @ORM\EntityListeners({"InterpretersOffice\Entity\Listener\InterpreterEntityListener"})
 * @ORM\Table(name="interpreters",uniqueConstraints={@ORM\UniqueConstraint(name="unique_ssn",columns={"ssn"})})
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
     * string rather than date because it will be encrypted
     *
     * @ORM\Column(type="string",length=125,nullable=true)
     *
     * @var string
     */
    protected $dob;


    /**
     *
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    protected $ssn;

    /**
     * date the security clearance expires.
     *
     * @ORM\Column(type="date",name="security_expiration_date",nullable=true)
     */
    protected $securityExpirationDate;

     /**
     * date fingerprints taken.
     *
     * @ORM\Column(type="date",name="fingerprint_date",nullable=true)
     */
    protected $fingerprintDate;

    /**
     * date oath taken.
     *
     * @ORM\Column(type="date",name="oath_date",nullable=true)
     */
    protected $oathDate;



    /**
     * working languages.
     *
     * @ORM\OneToMany(targetEntity="InterpreterLanguage",mappedBy="interpreter", cascade={"persist", "remove"},orphanRemoval=true)
     *
     * @var ArrayCollection of InterpreterLanguage
     */
    protected $interpreterLanguages;




    /*
      `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `honorific` enum('','Ms.','Mr.','Mrs.','Dr.') COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
        `active` enum('Y','N') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
        `freelance` enum('Y','N') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
        `ssn` varbinary(40) DEFAULT NULL,
        `dob` varbinary(40) DEFAULT NULL,
        `password` char(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
        `publish_public` enum('Y','N') COLLATE utf8_unicode_ci DEFAULT NULL,
        `publish_internal` enum('Y','N') COLLATE utf8_unicode_ci DEFAULT NULL,
        `security_clearance` date DEFAULT NULL,
        `contract_expiration` date DEFAULT NULL,
        `fingerprinted` date DEFAULT NULL,
        `oath` date DEFAULT NULL,

    */
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
     * @param string $dob
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
     * @return string
     */
    public function getDob()
    {
        return $this->dob;
    }

    /**
     * sets ssn
     *
     * @param string
     * @return Interpreter
     *
     */
    public function setSsn($ssn)
    {
        $this->ssn = $ssn;

        return $this;
    }

    /**
     * gets ssn
     * @return string
     *
     */
    public function getSsn()
    {
        return $this->ssn;
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
    "AllowRemove strategy for DoctrineModule hydrator requires both addInterpreterLanguages and
     removeInterpreterLanguages to be defined in InterpretersOffice\Entity\Interpreter
     entity domain code, but one or both seem to be missing"
     */
    /**
     * adds InterpreterLanguages.
     *
     * @param Collection $interpreterLanguages
     */
    public function addInterpreterLanguages(Collection $interpreterLanguages)
    {
        foreach ($interpreterLanguages as $interpreterLanguage) {
            $interpreterLanguage->setInterpreter($this);
            $this->interpreterLanguages->add($interpreterLanguage);
        }
    }
    /**
     * removes InterpreterLanguages.
     *
     * @param Collection $interpreterLanguages
     */
    public function removeInterpreterLanguages(Collection $interpreterLanguages)
    {
        foreach ($interpreterLanguages as $interpreterLanguage) {
            $this->interpreterLanguages->removeElement($interpreterLanguage);
        }
    }


    /**
     * Set securityExpirationDate
     *
     * @param \DateTime $securityExpirationDate
     *
     * @return Interpreter
     */
    public function setSecurityExpirationDate($securityExpirationDate)
    {
        $this->securityExpirationDate = $securityExpirationDate;

        return $this;
    }

    /**
     * Get securityExpirationDate
     *
     * @return \DateTime
     */
    public function getSecurityExpirationDate()
    {
        return $this->securityExpirationDate;
    }

    /**
     * Set fingerprintDate
     *
     * @param \DateTime $fingerprintDate
     *
     * @return Interpreter
     */
    public function setFingerprintDate($fingerprintDate)
    {
        $this->fingerprintDate = $fingerprintDate;

        return $this;
    }

    /**
     * Get fingerprintDate
     *
     * @return \DateTime
     */
    public function getFingerprintDate()
    {
        return $this->fingerprintDate;
    }

    /**
     * Set oathDate
     *
     * @param \DateTime $oathDate
     *
     * @return Interpreter
     */
    public function setOathDate($oathDate)
    {
        $this->oathDate = $oathDate;

        return $this;
    }

    /**
     * Get oathDate
     *
     * @return \DateTime
     */
    public function getOathDate()
    {
        return $this->oathDate;
    }
}
