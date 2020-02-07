<?php
/** module/InterpretersOffice/src/Entity/Interpreter.php */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DateTime;

//
/** @Entity @EntityListeners({"UserListener"}) */

/**
 * Entity representing an Interpreter.
 *
 * Interpreter is a subclass of Person, but is constrained to having only two
 * types of "Hat:" contract interpreter, or staff interpreter.
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
     * home phone number.
     *
     * @ORM\Column(type="string",length=16,nullable=true,name="home_phone")
     *
     * @var string
     */
    protected $home_phone;

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
     * social security number
     *
     * this will be encrypted, hence the column width
     *
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    protected $ssn;

    /**
     * date the security clearance expires.
     *
     * @ORM\Column(type="date",name="security_clearance_date",nullable=true)
     * @var \DateTime
     */
    protected $security_clearance_date;

     /**
     * date fingerprints taken.
     *
     * @ORM\Column(type="date",name="fingerprint_date",nullable=true)
     * @var \DateTime
     */
    protected $fingerprint_date;

    /**
     * date interpreters' oath was administered
     *
     * @ORM\Column(type="date",name="oath_date",nullable=true)
     * @var \DateTime
     */
    protected $oath_date;

     /**
     * date contract expires
     *
     * @ORM\Column(type="date",name="contract_expiration_date",nullable=true)
     * @var \DateTime
     */
    protected $contract_expiration_date;

    /**
    * date BOP security clearance was (last) submitted
    *
    * @ORM\Column(type="date",name="bop_form_submission_date",nullable=true)
    * @var \DateTime
    */
   protected $BOP_form_submission_date;

    /**
     * comments
     *
     * @ORM\Column(type="string",length=600,name="comments",nullable=false)
     * @var string
     */
     protected $comments = '';

    /**
     * whether to include in availability-solicitation emails
     *
     * @ORM\Column(type="boolean",nullable=false)
     * @var boolean
     */
    private $solicit_availability = false;



    /**
     * address line 1
     *
     * @ORM\Column(type="string",length=60,nullable=false)
     * @var string
     */
    protected $address1 = '';

    /**
     * address line 2
     *
     * @ORM\Column(type="string",length=60,nullable=false)
     * @var string
     */
    protected $address2 = '';

    /**
     * city
     *
     * @ORM\Column(type="string",length=40,nullable=false)
     * @var string
     */
    protected $city = '';

    /**
     * state or province
     *
     * @ORM\Column(type="string",length=40,nullable=false)
     * @var string
     */
    protected $state = '';

    /**
     * zip or postal code
     * @ORM\Column(type="string",length=16,nullable=false)
     * @var string
     */
    protected $zip = '';

    /**
     * country
     *
     * virtually everyone is in the US. please don't tell me we have to
     * normalize this :-)
     *
     * @ORM\Column(type="string",length=16,nullable=false)
     * @var string
     */
    protected $country = 'United States';

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
     * Set home phone.
     *
     * @param string $phone
     *
     * @return Interpreter
     */
    public function setHomePhone($phone)
    {

        $this->home_phone = $phone;

        return $this;
    }

    /**
     * Get home phone.
     *
     * @return string
     */
    public function getHomePhone()
    {
        return $this->formatPhone($this->home_phone);
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
        // temporary fix?
        if ($dob === '') {
            $dob = null;
        }
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
        // temporary fix?
        if ($ssn === '') {
            $ssn = null;
        }
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
     * returns array of Language objects for this Interpreter
     * @return Language[]
     */
    public function getLanguages()
    {
        $return = [];
        foreach ($this->interpreterLanguages as $ie) {
            $return[] = $ie->getLanguage();
        }

        return $return;
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
     * Set securityClearanceDate
     *
     * @param \DateTime $securityClearanceDate
     *
     * @return Interpreter
     */
    public function setSecurityClearanceDate(\DateTime $securityClearanceDate = null)
    {
        $this->security_clearance_date = $securityClearanceDate;

        return $this;
    }

    /**
     * Get securityClearanceDate
     *
     * @return \DateTime
     */
    public function getSecurityClearanceDate()
    {
        return $this->security_clearance_date;
    }

    /**
     * Set fingerprintDate
     *
     * @param \DateTime $fingerprintDate
     *
     * @return Interpreter
     */
    public function setFingerprintDate(\DateTime $fingerprintDate = null)
    {
        $this->fingerprint_date = $fingerprintDate;

        return $this;
    }

    /**
     * Get fingerprintDate
     *
     * @return \DateTime
     */
    public function getFingerprintDate()
    {
        return $this->fingerprint_date;
    }

    /**
     * Set oathDate
     *
     * @param \DateTime $oathDate
     *
     * @return Interpreter
     */
    public function setOathDate(\DateTime $oathDate = null)
    {
        $this->oath_date = $oathDate;

        return $this;
    }

    /**
     * Get oathDate
     *
     * @return \DateTime
     */
    public function getOathDate()
    {
        return $this->oath_date;
    }

    /**
     * Get contractExpirationDate
     *
     * @return \DateTime
     */
    public function getContractExpirationDate()
    {
        return $this->contract_expiration_date;
    }

    /**
     * Set contractExpirationDate
     *
     * @param \DateTime $contractExpirationDate
     *
     * @return Interpreter
     */
    public function setContractExpirationDate($contractExpirationDate)
    {
        $this->contract_expiration_date = $contractExpirationDate;

        return $this;
    }

    /**
     * gets BOP form submission date
     *
     * @return DateTime
     */
    public function getBOPFormSubmissionDate()
    {
        return $this->BOP_form_submission_date;
    }

    /**
     * sets BOP form submission date
     * @param DateTime $BOPFormSubmissionDate
     * @return Interpreter
     */
    public function setBOPFormSubmissionDate(DateTime $BOPFormSubmissionDate)
    {
        $this->BOP_form_submission_date = $BOPFormSubmissionDate;

        return $this;
    }

    /**
     * Set comments
     *
     * @param string $comments
     *
     * @return Interpreter
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set address1
     *
     * @param string $address1
     *
     * @return Interpreter
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;

        return $this;
    }

    /**
     * Get address1
     *
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * Set address2
     *
     * @param string $address2
     *
     * @return Interpreter
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * Get address2
     *
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Interpreter
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set state
     *
     * @param string $state
     *
     * @return Interpreter
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set zip
     *
     * @param string $zip
     *
     * @return Interpreter
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return Interpreter
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * gets solicity_availability
     *
     * @return boolean
     */
    public function getSolicitAvailability() : bool
    {
        return $this->solicit_availability;
    }

    /**
     * sets solicity_availability
     *
     * @return Interpreter
     */
    public function setSolicitAvailability(bool $flag) : Interpreter
    {
        $this->solicit_availability = $flag;

        return $this;
    }
}
