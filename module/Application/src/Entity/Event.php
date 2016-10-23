<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity representing a event involving an interpreter.
 *
 *
 * @ORM\Entity
 * @ORM\Table(name="events")
 * @ORM\HasLifecycleCallbacks
 */
class Event
{
    /**
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
     * The date and time are stored in separate columns because there are cases
     * where the date is known but the time is unknown or to-be-determined.
     *
     * @ORM\Column(type="time",nullable=true)
     */
    protected $end_time;

    /**
     * Every interpreter event implies a language.
     *
     * @ORM\ManyToOne(targetEntity="Language")
     * @ORM\JoinColumn(nullable=false)
     *
     * @var Language
     */
    protected $language;

    /**
     * Every event is of some type or other.
     *
     * @ORM\ManyToOne(targetEntity="EventType")
     * @ORM\JoinColumn(nullable=false)
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
     * While most events have a Judge, there are also cases where the identity of
     * the judge/person is unknown, irrelevant or not applicable.
     *
     * @ORM\ManyToOne(targetEntity="AnonymousJudge")
     * @ORM\JoinColumn(nullable=true)
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
     * @ORM\JoinColumn(nullable=true)
     *
     * @var Hat
     */
    protected $anonymousSubmitter;

    /**
     * @ORM\Column(type="string",length=15,nullable=false,options={"default":""})
     *
     * @var string
     */
    protected $docket;

    /**
     * Reason (if any) for cancelling the event.
     *
     * If an Event is cancelled with less than one business days' notice, it is
     * considered a belated cancellation, and we record the reason why. If it is
     * cancelled with more than one business day's notice, it should simply be
     * deleted.
     *
     * @ORM\ManyToOne(targetEntity="ReasonForCancellation")
     * @ORM\JoinColumn(nullable=true)
     *
     * @var ReasonForCancellation
     */
    protected $cancellationReason;

    /**
     * defendant(s) for whom an interpreter is required.
     *
     * @see DefendantName
     *
     * @ORM\ManyToMany(targetEntity="DefendantName")
     * @ORM\JoinTable(name="defendants_events",inverseJoinColumns={@ORM\JoinColumn(name="defendant_id", referencedColumnName="id")})
     *
     * @var DefendantName[]
     */
    protected $defendants;

    /**
     * @ORM\OneToMany(targetEntity="InterpreterEvent",mappedBy="event")
     *
     * @var InterpreterEvents[]
     */
    protected $interpretersAssigned;

    /**
     * comments for semi-public consumption.
     *
     * @ORM\Column(type="string",length=600,nullable=false,options={"default":""})
     *
     * @var string
     */
    protected $comments;

    /**
     * comments for managers and admins only.
     *
     * @ORM\Column(type="string",length=600,nullable=false,options={"default":""})
     *
     * @var string
     */
    protected $admin_comments;

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
     * sadly, nullable because of some mismanaged legacy data
     * @ORM\JoinColumn(nullable=true)
     */
    protected $createdBy;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    protected $modified;

    /**
     * last User who updated the Event.
     *
     * @ORM\ManyToOne(targetEntity="User")
     * sadly, nullable because of some mismanaged legacy data
     * @ORM\JoinColumn(nullable=true)
     */
    protected $modifiedBy;

    /* -------  setters and getter generated by Doctrine -------------- */

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->defendants = new ArrayCollection();
        $this->interpretersAssigned = ArrayCollection();
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
     * @return Event
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
     * Set endTime.
     *
     * @param \DateTime $endTime
     *
     * @return Event
     */
    public function setEndTime($endTime)
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
        $this->defendants[] = $defendant;

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
     * Get defendants.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDefendants()
    {
        return $this->defendants;
    }

    /**
     * Add interpretersAssigned.
     *
     * @param InterpreterEvent $interpretersAssigned
     *
     * @return Event
     */
    public function addInterpretersAssigned(InterpreterEvent $interpretersAssigned)
    {
        $this->interpretersAssigned[] = $interpretersAssigned;

        return $this;
    }

    /**
     * Remove interpretersAssigned.
     *
     * @param InterpreterEvent $interpretersAssigned
     */
    public function removeInterpretersAssigned(InterpreterEvent $interpretersAssigned)
    {
        $this->interpretersAssigned->removeElement($interpretersAssigned);
    }

    /**
     * Get interpretersAssigned.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInterpretersAssigned()
    {
        return $this->interpretersAssigned;
    }

    /**
     * Set createdBy.
     *
     * @param User $createdBy
     *
     * @return Event
     */
    public function setCreatedBy(User $createdBy = null)
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
}
