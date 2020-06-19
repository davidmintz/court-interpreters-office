<?php

/** module/InterpretersOffice/src/Entity/Event.php */

declare(strict_types=1);

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity representing a event involving an interpreter.
 *
 *
 * @ORM\Entity(repositoryClass="InterpretersOffice\Entity\Repository\EventRepository")
 * @ORM\Table(name="events",indexes={@ORM\Index(name="docket_idx", columns={"docket"})})
 * @ORM\HasLifecycleCallbacks
 * @ORM\EntityListeners({"InterpretersOffice\Entity\Listener\EventEntityListener"})
 */
class Event implements Interpretable
{
    /**
     * entity id.
     *
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer",options={"unsigned":true})
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
     * @ORM\Column(type="time",nullable=true)
     */
    protected $time;

    /**
     * time at which the event ended.
     *
     * @ORM\Column(type="time",nullable=true)
     */
    protected $end_time;

    /**
     * date on which the service was requested.
     *
     * @ORM\Column(type="date",nullable=false,name="submission_date")
     * @var \DateTime
     */
    protected $submission_date;

    /**
     * time at which the event was requested.
     *
     * this field is obligatory but there is legacy data from a time
     * when it wasn't. also, this makes it easier to change our mind later on.
     *
     * @ORM\Column(type="time",nullable=true,name="submission_time")
     * @var \DateTime
     */
    protected $submission_time;

    /**
     * Every interpreter event implies a language.
     *
     * @ORM\ManyToOne(targetEntity="Language",inversedBy="events") //,inversedBy="events"
     * @ORM\JoinColumn(nullable=false)
     *
     * @var Language
     */
    protected $language;

    /**
     * Every event is of some type or other.
     *
     * @ORM\ManyToOne(targetEntity="EventType",inversedBy="events")
     * @ORM\JoinColumn(nullable=false,name="event_type_id")
     *
     * @var EventType
     */
    protected $event_type;

    /**
     * Most events have a Judge.
     *
     * @ORM\ManyToOne(targetEntity="Judge") //,inversedBy="events"
     * @ORM\JoinColumn(nullable=true)
     *
     * @var Judge
     */
    protected $judge;

    /**
     * Anonymous or generic judge.
     *
     * While most events have a Judge, there are also cases where the identity of
     * the judge/person is unknown, irrelevant or not applicable.
     *
     * @ORM\ManyToOne(targetEntity="AnonymousJudge")
     * @ORM\JoinColumn(nullable=true,name="anonymous_judge_id")
     *
     * @var AnonymousJudge
     */
    protected $anonymous_judge;

    /**
     * The interpreter is requested by a Person (submitter).
     *
     * @ORM\ManyToOne(targetEntity="Person")//,inversedBy="events"
     * @ORM\JoinColumn(nullable=true)
     *
     * @var Person
     */
    protected $submitter;

    /**
     * "Hat" type of an anonymous submitter.
     *
     * In some cases we record only the generic person-type or agency
     * that submitted the request, rather than a specific Person.
     *
     * @ORM\ManyToOne(targetEntity="Hat")
     * @ORM\JoinColumn(nullable=true,name="anonymous_submitter_id")
     *
     * @var Hat
     */
    protected $anonymous_submitter;

    /**
     * the docket number.
     *
     * @ORM\Column(type="string",length=15,nullable=false,options={"default":""})
     *
     * @var string
     */
    protected $docket = '';

    /**
     * event location.
     *
     * @ORM\ManyToOne(targetEntity="Location",inversedBy="events")
     * @ORM\JoinColumn(nullable=true)
     *
     * @var Location
     */
    protected $location;

    /**
     * Reason (if any) for cancelling the event.
     *
     * If an Event is cancelled with less than one business days' notice, it is
     * considered a belated cancellation, and we record the reason why. If it is
     * cancelled with more than one business day's notice, it should simply be
     * deleted.
     *
     * @ORM\ManyToOne(targetEntity="ReasonForCancellation")
     * @ORM\JoinColumn(nullable=true,name="cancellation_reason_id")
     *
     * @var ReasonForCancellation
     */
    protected $cancellation_reason;



    // cascade={"persist","remove"},orphanRemoval=true,fetch="EAGER") ??
    /**
     * Defendant names.
     *
     * @ORM\ManyToMany(targetEntity="\InterpretersOffice\Entity\Defendant")
     * @ORM\JoinTable(name="defendants_events",
     *      joinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="defendant_id", referencedColumnName="id")}
     * )
     *
     * @var ArrayCollection
     */
    protected $defendants;


    /**
     * InterpreterEvent entities
     *
     * @ORM\OneToMany(targetEntity="InterpreterEvent",mappedBy="event",cascade={"persist", "remove"},orphanRemoval=true,fetch="EAGER")
     *
     * @var Collection
     */
    protected $interpreterEvents;

    /**
     * comments for semi-public consumption.
     *
     * @ORM\Column(type="string",length=600,nullable=false,options={"default":""})
     *
     * @var string
     */
    protected $comments = '';

    /**
     * comments for managers and admins only.
     *
     * @ORM\Column(type="string",length=600,nullable=false,options={"default":""})
     *
     * @var string
     */
    protected $admin_comments = '';

    /* ------------ metadata fields ------------------ */

    /**
     * date/time when Event was created.
     *
     * @ORM\Column(type="datetime",nullable=false)
     *
     * @var \DateTime
     */
    protected $created;

    /**
     * User who created the Event.
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false,name="created_by_id")
     */
    protected $created_by;

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
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=true,name="modified_by_id")
     */
    protected $modified_by;

    /**
     * Interpreters assigned to this Event.
     *
     * This is not managed by Doctrine, but for a convenience method for
     * getting just the Interpreter entities from InterpreterEvents
     * @var array
     */
    private $interpreters = [];

    /**
     * soft-deletion flag
     *
     * @ORM\Column(type="boolean",options={"nullable":false,"default":false})
     * @var bool true if Event has been (soft-)deleted
     *
     */
    private $deleted = false;


    /* -------  (mostly) generated by Doctrine -------------- */

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->defendants = new ArrayCollection();
        $this->interpreterEvents = new ArrayCollection();
    }

    /**
     * returns a short description
     *
     * @return string
     */
    public function describe() : string
    {
        $type = (string)$this->getEventType();
        $datetime = $this->getDate()->format('d-M-Y');
        if ($this->getTime()) {
            $datetime .= ' at '.$this->getTime()->format('g:i a');
        }
        $return = sprintf('%s %s, %s', $this->getLanguage(), $type, $datetime);
        $more = [];
        if ($this->getJudge()) {
            $more[] = $this->getJudge()->getLastName();
        }
        $docket = $this->getDocket();
        if ($docket) {
            $more[] = $docket;
        }
        if ($more) {
            $return .= sprintf(' (%s)', implode(', ', $more));
        }

        return $return;
    }

    /**
     * returns judge or anonymous judge as string
     *
     * @return string
     */
    public function getStringifiedJudgeOrWhatever() : string
    {
        if ($this->judge) {
            $string = $this->judge->getFirstName().' ';
            if ($this->judge->getMiddleName()) {
                $string .= $this->judge->getMiddleName().' ';
            }
            $string .= $this->judge->getLastName().', ';
            $string .= (string)$this->judge->getFlavor();
            return $string;
        } elseif ($this->anonymous_judge) {
            return $this->anonymous_judge->getName();
        }
        return '';
    }
    /**
     * Get id.
     *
     * @return int
     */
    public function getId() :? int
    {
        return $this->id;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Event
     */
    public function setDate(\DateTime $date) : Event
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate() : ?\DateTime
    {
        return $this->date;
    }

    /**
     * Set time.
     *
     * @param \DateTime $time
     *
     * @return Event
     */
    public function setTime(\DateTime $time = null) : Event
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time.
     *
     * @return \DateTime
     */
    public function getTime() : ? \DateTime
    {
        return $this->time;
    }

    /**
     * Set endTime.
     *
     * @param \DateTime $endTime
     *
     * @return Event
     */
    public function setEndTime(\DateTime $end_time = null) : Event
    {
        $this->end_time = $end_time;

        return $this;
    }

    /**
     * Get endTime.
     *
     * @return \DateTime
     */
    public function getEndTime() : ? \DateTime
    {
        return $this->end_time;
    }

    /**
     * Set docket.
     *
     * @param string $docket
     *
     * @return Event
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
    public function getDocket() : string
    {
        return $this->docket;
    }

    /**
     * set Location.
     *
     * @param Location
     *
     * @return Event
     */
    public function setLocation(Location $location = null) : Event
    {
        $this->location = $location;

        return $this;
    }

    /**
     * get Location.
     *
     * @return Location
     */
    public function getLocation() : ?Location
    {
        return $this->location;
    }

    /**
     * Set comments.
     *
     * @param string $comments
     *
     * @return Event
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
     * Set adminComments.
     *
     * @param string $adminComments
     *
     * @return Event
     */
    public function setAdminComments($adminComments)
    {
        $this->admin_comments = $adminComments;

        return $this;
    }

    /**
     * Get adminComments.
     *
     * @return string
     */
    public function getAdminComments()
    {
        return $this->admin_comments;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return Event
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
     * @param \DateTime $modified
     *
     * @return Event
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified.
     *
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set language.
     *
     * @param Language $language
     *
     * @return Event
     */
    public function setLanguage(Language $language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language.
     *
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set event_type.
     *
     * @param EventType $event_type
     *
     * @return Event
     */
    public function setEventType(EventType $event_type)
    {
        $this->event_type = $event_type;

        return $this;
    }

    /**
     * Get event_type.
     *
     * @return EventType
     */
    public function getEventType()
    {
        return $this->event_type;
    }

    /**
     * Set judge.
     *
     * @param Judge $judge
     *
     * @return Event
     */
    public function setJudge(Judge $judge = null)
    {
        $this->judge = $judge;

        return $this;
    }

    /**
     * Get judge.
     *
     * @return Judge
     */
    public function getJudge()
    {
        return $this->judge;
    }

    /**
     * Set anonymous_judge.
     *
     * @param AnonymousJudge $anonymous_judge
     *
     * @return Event
     */
    public function setAnonymousJudge(AnonymousJudge $anonymous_judge = null)
    {
        $this->anonymous_judge = $anonymous_judge;

        return $this;
    }

    /**
     * Get anonymous_judge.
     *
     * @return AnonymousJudge
     */
    public function getAnonymousJudge()
    {
        return $this->anonymous_judge;
    }

    /**
     * Set submitter.
     *
     * @param Person $submitter
     *
     * @return Event
     */
    public function setSubmitter(Person $submitter = null)
    {
        $this->submitter = $submitter;

        return $this;
    }

    /**
     * Get submitter.
     *
     * @return Person
     */
    public function getSubmitter()
    {
        return $this->submitter;
    }

    /**
     * Set anonymous_submitter.
     *
     * @param Hat $anonymous_submitter
     *
     * @return Event
     */
    public function setAnonymousSubmitter(Hat $anonymous_submitter = null)
    {
        $this->anonymous_submitter = $anonymous_submitter;

        return $this;
    }

    /**
     * Get anonymous_submitter.
     *
     * @return Hat
     */
    public function getAnonymousSubmitter()
    {
        return $this->anonymous_submitter;
    }


    /**
     * Set submissionDate
     *
     * @param \DateTime $submissionDate
     *
     * @return Event
     */
    public function setSubmissionDate(\DateTime $submission_date) : Event
    {
        $this->submission_date = $submission_date;

        return $this;
    }

    /**
     * Get submissionDate
     *
     * @return \DateTime
     */
    public function getSubmissionDate()
    {
        return $this->submission_date;
    }

    /**
     * Set submissionTime
     *
     * @param \DateTime $submissionTime
     *
     * @return Event
     */
    public function setSubmissionTime(\DateTime $submission_time)
    {
        $this->submission_time = $submission_time;

        return $this;
    }

    /**
     * Get submissionTime
     *
     * @return \DateTime
     */
    public function getSubmissionTime() : ?\DateTime
    {
        return $this->submission_time;
    }

    /**
     * Set cancellationReason.
     *
     * @param ReasonForCancellation $cancellationReason
     *
     * @return Event
     */
    public function setCancellationReason(ReasonForCancellation $cancellation_reason = null)
    {
        $this->cancellation_reason = $cancellation_reason;

        return $this;
    }

    /**
     * Get cancellationReason.
     *
     * @return ReasonForCancellation
     */
    public function getCancellationReason()
    {
        return $this->cancellation_reason;
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
     * @param Collection $defendants
     *
     * @return Event
     */
    public function addDefendants(Collection $defendants)
    {
        foreach ($defendants as $defendant) {
            $this->defendants->add($defendant);
        }

        return $this;
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

    /**
     * adds InterpreterEvents
     *
     * @param Collection $interpreterEvents
     */
    public function addInterpreterEvents(Collection $interpreterEvents)
    {
        foreach ($interpreterEvents as $interpreterEvent) {
            $interpreterEvent->setEvent($this);
            $this->interpreterEvents->add($interpreterEvent);
        }
    }

    /**
     * removes InterpretersEvents
     *
     * @param Collection $interpreterEvents
     */
    public function removeInterpreterEvents(Collection $interpreterEvents)
    {
        foreach ($interpreterEvents as $interpreterEvent) {
            $interpreterEvent->setEvent(null);
            $this->interpreterEvents->removeElement($interpreterEvent);
        }
    }

    /**
     * Get interpreterEvents.
     *
     * @return Collection
     */
    public function getInterpreterEvents()
    {
        return $this->interpreterEvents;
    }

    /**
     * Set created_by.
     *
     * @param User $created_by
     *
     * @return Event
     */
    public function setCreatedBy(User $created_by)
    {
        $this->created_by = $created_by;

        return $this;
    }

    /**
     * Get created_by.
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * Set modified_by.
     *
     * @param User $modified_by
     *
     * @return Event
     */
    public function setModifiedBy(User $modified_by = null)
    {
        $this->modified_by = $modified_by;

        return $this;
    }

    /**
     * Get modified_by.
     *
     * @return User
     */
    public function getModifiedBy()
    {
        return $this->modified_by;
    }

    /**
     * Set deleted.
     *
     * @param bool $deleted
     *
     * @return Request
     */
    public function setDeleted(bool $deleted) : Event
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get pending.
     *
     * @return bool
     */
    public function getDeleted() : bool
    {
        return $this->deleted;
    }

    /**
     * Proxies to getDeleted().
     *
     * @return bool
     */
    public function isDeleted() : bool
    {
        return $this->getDeleted();
    }

    /**
     * convenience method for assigning an interpreter.
     *
     * @param Interpreter $interpreter
     *
     * @return Event
     */
    public function assignInterpreter(Interpreter $interpreter)
    {
        $this->getInterpreterEvents()
                ->add(new InterpreterEvent($interpreter, $this));

        return $this;
    }

    /**
     * gets array of our Interpreter entities
     *
     * @return Interpreter[]
     */
    public function getInterpreters() : Array
    {
        $ie_collection = $this->getInterpreterEvents();
        if (! $ie_collection->count()) {
            return [];
        }
        if ($this->interpreters) {
            return $this->interpreters;
        }
        $this->interpreters = array_map(function ($ie) {
            return $ie->getInterpreter();
        }, $ie_collection->toArray());

        return $this->interpreters;
    }

    /**
     * Lifecycle callback/sanity check for submitter and judge properties.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     *
     * @throws \RuntimeException
     */
    public function onSave()
    {
        if (! ($this->anonymous_submitter == null xor $this->submitter === null)) {
            throw new \RuntimeException(
                'Event entity submitter and anonymous_submitter properties: '
                    .' one must be null and the other not-null'
            );
        }
        if (! $this->getSubmitter() && !$this->getAnonymousSubmitter()->getAnonymity()) {
            throw new \RuntimeException(sprintf(
                'The request submitter of type "%s" cannot be anonymous. This may be a bug in the application\'s input validation',
                (string)$this->getAnonymousSubmitter()
            ));
        }
        if (! ($this->anonymous_judge === null xor $this->judge === null)) {
            $debug = "\nanonymous_judge: " .(is_null($this->anonymous_judge) ? "null" : "not null");
            $debug .= "\nJudge: " .(is_null($this->judge) ? "null" : "not null");
            throw new \RuntimeException(
                'Event entity judge and anonymous_judge properties: '
                    .' one must be null and the other not-null. ' . $debug
            );
        }
    }
}
