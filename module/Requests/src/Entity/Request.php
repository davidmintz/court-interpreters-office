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






}
