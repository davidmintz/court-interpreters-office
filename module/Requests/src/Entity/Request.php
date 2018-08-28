<?php /* module/Requests/src/Entity/Request.php */

namespace InterpretersOffice\Requests\Entity;

use Doctrine\ORM\Mapping as ORM;
use InterpretersOffice\Entity\Person;

//use InterpretersOffice\Entity;

/**
 * @ORM\Entity(repositoryClass="InterpretersOffice\Requests\Entity\RequestRepository");
 * @ORM\Table(name="requests")
 */
class Request
{

    /**
     * entity id.
     *
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    protected $id;

    /**
     * date on which the event takes place.
     *
     * @ORM\Column(type="date",nullable=false)
     */
    protected $date;

    /**
     * time at which the event takes place.
     *
     * The date and time are stored in separate columns because there are cases
     * where the date is known but the time is unknown or to-be-determined.
     *
     * @ORM\Column(type="time",nullable=false)
     */
    protected $time;

    /**
     * language.
     *
     * @ORM\ManyToOne(targetEntity="\InterpretersOffice\Entity\Language") //,inversedBy="events"
     * @ORM\JoinColumn(nullable=false)
     *
     * @var Language
     */
    protected $language;

    /**
     * Every event is of some type or other.
     *
     * @ORM\ManyToOne(targetEntity="\InterpretersOffice\Entity\EventType",inversedBy="events")
     * @ORM\JoinColumn(nullable=false,name="event_type_id")
     *
     * @var EventType
     */
    protected $eventType;

    /**
     * Judge.
     *
     * @ORM\ManyToOne(targetEntity="\InterpretersOffice\Entity\Judge") //,inversedBy="events"
     * @ORM\JoinColumn(nullable=true)
     *
     * @var Judge
     */
    protected $judge;

    /**
     * Anonymous or generic judge.
     *
     * While most events have a Judge, in a few cases the identity of
     * the judge/person is unknown, irrelevant or not applicable.
     *
     * @ORM\ManyToOne(targetEntity="\InterpretersOffice\Entity\AnonymousJudge")
     * @ORM\JoinColumn(nullable=true,name="anonymous_judge_id")
     *
     * @var AnonymousJudge
     */
    protected $anonymousJudge;

    /**
     * The interpreter is requested by a Person (submitter). For requests
     * submitted through this application (rather than phone, email, etc),
     * the submitter_id identical the current user/person who creates
     * the Request entity.
     *
     * @ORM\ManyToOne(targetEntity="\InterpretersOffice\Entity\Person")//,inversedBy="events"
     * @ORM\JoinColumn(nullable=true)
     *
     * @var Entity\Person
     */
    protected $submitter;



    /**
     * the docket number.
     *
     * @ORM\Column(type="string",length=15,nullable=false,options={"default":""})
     *
     * @var string
     */
    protected $docket = '';


    /**
     * location.
     *
     * @ORM\ManyToOne(targetEntity="\InterpretersOffice\Entity\Location")
     * @ORM\JoinColumn(nullable=true)
     *
     * @var Entity\Location
     */
    protected $location;

    /**
     * date/time when Event was created.
     *
     * @ORM\Column(type="datetime",nullable=false)
     *
     * @var \DateTime
     */
    protected $created;

    /**
     * timestamp of last update.
     *
     * @ORM\Column(type="datetime",nullable=true)
     *
     * @var \DateTime
     */
    protected $modified;

    /**
     * last User who updated the Event.
     *
     * @ORM\ManyToOne(targetEntity="\InterpretersOffice\Entity\User")
     * @ORM\JoinColumn(nullable=true,name="modified_by_id")
     */
    protected $modifiedBy;

    /**
     * comments.
     *
     * @ORM\Column(type="string",length=600,nullable=false,options={"default":""})
     *
     * @var string
     */
    protected $comments = '';

    /**
     * scheduled event
     *
     * @ORM\OneToOne(targetEntity="\InterpretersOffice\Entity\Event")
     * @var Entity\Event
     */
    protected $event;

    /**
     * If the event property is null, it means that the event is either pending
     * or else was once scheduled and later deleted. This flag enables us to
     * determine which is the case.
     *
     * @ORM\Column(type="boolean",options={"nullable":false,"default":true})
     *
     * @var bool true if request is "pending"
     */
    protected $pending = true;

    /**
     * Defendant names.
     *
     * @ORM\ManyToMany(targetEntity="\InterpretersOffice\Entity\Defendant")
     * @ORM\JoinTable(name="defendants_requests",
     *      joinColumns={@ORM\JoinColumn(name="defendant_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="request_id", referencedColumnName="id")}
     * )
     *
     * @var ArrayCollection
     */
    protected $defendants;

    /*CREATE TABLE `requests` (
  `id` mediumint(8) unsigned NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `judge_id` smallint(5) unsigned DEFAULT NULL,
  `anonymous_judge_id` smallint(5) unsigned DEFAULT NULL,
  `event_type_id` smallint(5) unsigned NOT NULL,
  `language_id` smallint(6) unsigned NOT NULL,
  `docket` varchar(14) COLLATE utf8_unicode_ci NOT NULL,
  `location_id` smallint(5) unsigned DEFAULT NULL,
  `submitter_id` smallint(5) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `modified_by_id` smallint(5) unsigned NOT NULL,
  `comments` varchar(500) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `event_id` mediumint(8) unsigned DEFAULT NULL,
  `pending` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_event_id` (`event_id`),
  KEY `evt_id` (`event_id`),
  KEY `submitter_id` (`submitter_id`),
  KEY `event_type_id` (`event_type_id`),
  KEY `location_id` (`location_id`),
  KEY `language_id` (`language_id`),
  KEY `modified_by_id` (`modified_by_id`),
  KEY `judge_id` (`judge_id`),
  KEY `anonymous_judge_id` (`anonymous_judge_id`),
  CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`),
  CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `requests_ibfk_3` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`),
  CONSTRAINT `requests_ibfk_4` FOREIGN KEY (`modified_by_id`) REFERENCES `users` (`id`),
  CONSTRAINT `requests_ibfk_5` FOREIGN KEY (`submitter_id`) REFERENCES `people` (`id`),
  CONSTRAINT `requests_ibfk_6` FOREIGN KEY (`judge_id`) REFERENCES `judges` (`id`),
  CONSTRAINT `requests_ibfk_7` FOREIGN KEY (`anonymous_judge_id`) REFERENCES `anonymous_judges` (`id`),
  CONSTRAINT `requests_ibfk_8` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL
)*/

/**
     * Constructor
     */
    public function __construct()
    {
        $this->defendants = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Request
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set time.
     *
     * @param \DateTime $time
     *
     * @return Request
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time.
     *
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set docket.
     *
     * @param string $docket
     *
     * @return Request
     */
    public function setDocket($docket)
    {
        $this->docket = $docket;

        return $this;
    }

    /**
     * Get docket.
     *
     * @return string
     */
    public function getDocket()
    {
        return $this->docket;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return Request
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set modified.
     *
     * @param \DateTime|null $modified
     *
     * @return Request
     */
    public function setModified($modified = null)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified.
     *
     * @return \DateTime|null
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set comments.
     *
     * @param string $comments
     *
     * @return Request
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments.
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set pending.
     *
     * @param bool $pending
     *
     * @return Request
     */
    public function setPending($pending)
    {
        $this->pending = $pending;

        return $this;
    }

    /**
     * Get pending.
     *
     * @return bool
     */
    public function getPending()
    {
        return $this->pending;
    }

    /**
     * Set language.
     *
     * @param \InterpretersOffice\Entity\Language $language
     *
     * @return Request
     */
    public function setLanguage(\InterpretersOffice\Entity\Language $language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language.
     *
     * @return \InterpretersOffice\Entity\Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set eventType.
     *
     * @param \InterpretersOffice\Entity\EventType $eventType
     *
     * @return Request
     */
    public function setEventType(\InterpretersOffice\Entity\EventType $eventType)
    {
        $this->eventType = $eventType;

        return $this;
    }

    /**
     * Get eventType.
     *
     * @return \InterpretersOffice\Entity\EventType
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * Set judge.
     *
     * @param \InterpretersOffice\Entity\Judge|null $judge
     *
     * @return Request
     */
    public function setJudge(\InterpretersOffice\Entity\Judge $judge = null)
    {
        $this->judge = $judge;

        return $this;
    }

    /**
     * Get judge.
     *
     * @return \InterpretersOffice\Entity\Judge|null
     */
    public function getJudge()
    {
        return $this->judge;
    }

    /**
     * Set anonymousJudge.
     *
     * @param \InterpretersOffice\Entity\AnonymousJudge|null $anonymousJudge
     *
     * @return Request
     */
    public function setAnonymousJudge(\InterpretersOffice\Entity\AnonymousJudge $anonymousJudge = null)
    {
        $this->anonymousJudge = $anonymousJudge;

        return $this;
    }

    /**
     * Get anonymousJudge.
     *
     * @return \InterpretersOffice\Entity\AnonymousJudge|null
     */
    public function getAnonymousJudge()
    {
        return $this->anonymousJudge;
    }

    /**
     * Set submitter.
     *
     * @param \InterpretersOffice\Entity\Person|null $submitter
     *
     * @return Request
     */
    public function setSubmitter(\InterpretersOffice\Entity\Person $submitter = null)
    {
        $this->submitter = $submitter;

        return $this;
    }

    /**
     * Get submitter.
     *
     * @return \InterpretersOffice\Entity\Person|null
     */
    public function getSubmitter()
    {
        return $this->submitter;
    }

    /**
     * Set location.
     *
     * @param \InterpretersOffice\Entity\Location|null $location
     *
     * @return Request
     */
    public function setLocation(\InterpretersOffice\Entity\Location $location = null)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location.
     *
     * @return \InterpretersOffice\Entity\Location|null
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set modifiedBy.
     *
     * @param \InterpretersOffice\Entity\User|null $modifiedBy
     *
     * @return Request
     */
    public function setModifiedBy(\InterpretersOffice\Entity\User $modifiedBy = null)
    {
        $this->modifiedBy = $modifiedBy;

        return $this;
    }

    /**
     * Get modifiedBy.
     *
     * @return \InterpretersOffice\Entity\User|null
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }

    /**
     * Set event.
     *
     * @param \InterpretersOffice\Entity\Event|null $event
     *
     * @return Request
     */
    public function setEvent(\InterpretersOffice\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event.
     *
     * @return \InterpretersOffice\Entity\Event|null
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Add defendant.
     *
     * @param \InterpretersOffice\Entity\Defendant $defendant
     *
     * @return Request
     */
    public function addDefendant(\InterpretersOffice\Entity\Defendant $defendant)
    {
        $this->defendants[] = $defendant;

        return $this;
    }

    /**
     * Remove defendant.
     *
     * @param \InterpretersOffice\Entity\Defendant $defendant
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeDefendant(\InterpretersOffice\Entity\Defendant $defendant)
    {
        return $this->defendants->removeElement($defendant);
    }

    /**
     * Get defendants.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDefendants()
    {
        return $this->defendants;
    }

    /**
     * adds Defendants.
     *
     * @param Collection $judges
     */
    public function addDefendants(Collection $defendants)
    {
        foreach ($defendants as $defendant) {
            $this->defendants->add($defendant);
        }
    }

    /**
     * removes defendants.
     *
     * @param Collection $defendants
     */
    public function removeDefendants(Collection $defendants)
    {
        foreach ($defendants as $defendant) {
            $this->defendants->removeElement($defendant);
        }
    }
}
