<?php

/** module/InterpretersOffice/src/Entity/Event.php */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity representing a event involving an interpreter.
 *
 *
 * @ORM\Entity(repositoryClass="InterpretersOffice\Entity\Repository\EventRepository")
 * @ORM\Table(name="events")
 * @ORM\HasLifecycleCallbacks
 * @ORM\EntityListeners({"InterpretersOffice\Entity\Listener\EventEntityListener"})
 */
class Event
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
     * @ORM\ManyToOne(targetEntity="Language",inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     *
     * @var Language
     */
    protected $language;

    /**
     * Every event is of some type or other.
     *
     * @ORM\ManyToOne(targetEntity="EventType")
     * @ORM\JoinColumn(nullable=false,name="event_type_id")
     *
     * @var EventType
     */
    protected $eventType;

    /**
     * Most events have a Judge.
     *
     * @ORM\ManyToOne(targetEntity="Judge")
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
    protected $anonymousJudge;

    /**
     * The interpreter is requested by a Person (submitter).
     *
     * @ORM\ManyToOne(targetEntity="Person")
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
    protected $anonymousSubmitter;

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
     * @ORM\ManyToOne(targetEntity="Location")
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
    protected $cancellationReason;

    /* FROM our Request entity in the older project....
     * 
     * @ORM\ManyToMany(targetEntity="Application\Entity\DefendantName",fetch="EAGER") 
     * @ORM\JoinTable(name="defendants_requests",
     *      joinColumns={@ORM\JoinColumn(name="request_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="defendant_id", referencedColumnName="deft_id")}
     * )
     *  //, unique=true ?
     * cribbed from:
     * http://doctrine-orm.readthedocs.org/en/latest/reference/annotations-reference.html#annref-manytomany
     */
    
    
    /**
     * defendant(s) for whom an interpreter is required.
     *
     * @see DefendantName
     *
     * @ORM\ManyToMany(targetEntity="DefendantName",fetch="EAGER",cascade="remove") //,inversedBy="Events",
     * @ORM\JoinTable(name="defendants_events",
     *  joinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="defendant_id", referencedColumnName="id")})
     *
     * @var Collection
     */
    protected $defendantNames;

    /**
     * Interpreters assigned to this event.
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
    protected $createdBy;

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
    protected $modifiedBy;


    /* -------  (mostly) generated by Doctrine -------------- */

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->defendantNames = new ArrayCollection();
        $this->interpreterEvents = new ArrayCollection();
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
     * @return Event
     */
    public function setDate(\DateTime $date)
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
     * @return Event
     */
    public function setTime(\DateTime $time)
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
     * Set endTime.
     *
     * @param \DateTime $endTime
     *
     * @return Event
     */
    public function setEndTime(\DateTime $endTime = null)
    {
        $this->end_time = $endTime;

        return $this;
    }

    /**
     * Get endTime.
     *
     * @return \DateTime
     */
    public function getEndTime()
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
    public function getDocket()
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
    public function setLocation(Location $location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * get Location.
     *
     * @return Location
     */
    public function getLocation()
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
     * Set eventType.
     *
     * @param EventType $eventType
     *
     * @return Event
     */
    public function setEventType(EventType $eventType)
    {
        $this->eventType = $eventType;

        return $this;
    }

    /**
     * Get eventType.
     *
     * @return EventType
     */
    public function getEventType()
    {
        return $this->eventType;
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
     * Set anonymousJudge.
     *
     * @param AnonymousJudge $anonymousJudge
     *
     * @return Event
     */
    public function setAnonymousJudge(AnonymousJudge $anonymousJudge = null)
    {
        $this->anonymousJudge = $anonymousJudge;

        return $this;
    }

    /**
     * Get anonymousJudge.
     *
     * @return AnonymousJudge
     */
    public function getAnonymousJudge()
    {
        return $this->anonymousJudge;
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
     * Set anonymousSubmitter.
     *
     * @param Hat $anonymousSubmitter
     *
     * @return Event
     */
    public function setAnonymousSubmitter(Hat $anonymousSubmitter = null)
    {
        $this->anonymousSubmitter = $anonymousSubmitter;

        return $this;
    }

    /**
     * Get anonymousSubmitter.
     *
     * @return Hat
     */
    public function getAnonymousSubmitter()
    {
        return $this->anonymousSubmitter;
    }


    /**
     * Set submissionDate
     *
     * @param \DateTime $submissionDate
     *
     * @return Event
     */
    public function setSubmissionDate(\DateTime $submissionDate)
    {
        $this->submission_date = $submissionDate;

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
    public function setSubmissionTime(\DateTime $submissionTime)
    {
        $this->submission_time = $submissionTime;

        return $this;
    }

    /**
     * Get submissionTime
     *
     * @return \DateTime
     */
    public function getSubmissionTime()
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
    public function setCancellationReason(ReasonForCancellation $cancellationReason = null)
    {
        $this->cancellationReason = $cancellationReason;

        return $this;
    }

    /**
     * Get cancellationReason.
     *
     * @return ReasonForCancellation
     */
    public function getCancellationReason()
    {
        return $this->cancellationReason;
    }
    
    
    
    /**
     * Add defendant.
     *
     * @param DefendantName $defendant
     *
     * @return Event
     */
    public function addDefendant(DefendantName $defendant)
    {
        $this->defendantNames->add($defendant);

        return $this;
    }

    /**
     * Remove defendant.
     *
     * @param DefendantName $defendant
     */
    public function removeDefendant(DefendantName $defendant)
    {
        $this->defendants->removeElement($defendant);
    }

    /**
     * Proxies to getDefendantNames();
     *
     * @return Collection
     */
    public function getDefendants()
    {
        return $this->getDefendantNames();
    }
    
    /**
     * Get defendants.
     *
     * @return Collection
     */
    public function getDefendantNames()
    {
        return $this->defendantNames;
    }

    /**
     * adds DefendantNames
     * 
     * @param Collection $defendantNames
     */
    public function addDefendantNames(Collection $defendantNames)
    {
        //printf("Here's Johnny in %s with %d elements<br>",__METHOD__, $defendantNames->count());
        foreach ($defendantNames as $defendantName) {           
            $this->defendantNames->add($defendantName);
        }
    }
    
    /**
     * removes DefendantNames
     * 
     * @param Collection $defendantNames
     */
    public function removeDefendantNames(Collection $defendantNames)
    {       
        foreach ($defendantNames as $defendantName) {     
            $this->defendantNames->removeElement($defendantName);
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
     * Set createdBy.
     *
     * @param User $createdBy
     *
     * @return Event
     */
    public function setCreatedBy(User $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy.
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set modifiedBy.
     *
     * @param User $modifiedBy
     *
     * @return Event
     */
    public function setModifiedBy(User $modifiedBy = null)
    {
        $this->modifiedBy = $modifiedBy;

        return $this;
    }

    /**
     * Get modifiedBy.
     *
     * @return User
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
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
     * Lifecycle callback/sanity check for submitter and judge properties.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     *
     * @throws \RuntimeException
     */
    public function onSave()
    {
        if (! ($this->anonymousSubmitter == null xor $this->submitter === null) ) {
            throw new \RuntimeException(
                'Event entity submitter and anonymousSubmitter properties: '
                    .' one must be null and the other not-null'
            );
        }
        if (! ($this->anonymousJudge === null xor $this->judge === null) ) {
            $debug = "\nanonymousJudge: " .(is_null($this->anonymousJudge)?"null":"not null");
            $debug .= "\nJudge: " .(is_null($this->judge)?"null":"not null");
            throw new \RuntimeException(
                'Event entity judge and anonymousJudge properties: '
                    .' one must be null and the other not-null. ' . $debug
            );
        }
    }
}
