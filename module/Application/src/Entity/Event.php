<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/** 
 * Entity representing a event involving an interpreter.
 * 
 * 
 * @ORM\Entity  
 * @ORM\Table(name="events") 
 */


class Event {

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    protected $id;
    
    /**
     * date on which the event takes place
     * 
     * @ORM\Column(type="date",nullable=false)
     */
    protected $date;
    
    
    /**
     * time at which the event takes place
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
     * @var Language
     */
    protected $language;
    
    
    /**
     * Every event is of some type or other.
     * 
     * @ORM\ManyToOne(targetEntity="EventType")
     * @ORM\JoinColumn(nullable=false)
     * @var EventType
     */
    protected $eventType;
    
    /**
     * Most events have a Judge.
     * 
     * @ORM\ManyToOne(targetEntity="Judge")
     * @ORM\JoinColumn(nullable=true)
     * @var Judge
     */
    protected $judge;
    
    /**
     * While most events have a Judge, there are also cases where the identity of
     * the judge/person is unknown, irrelevant or not applicable. 
     * 
     * @ORM\ManyToOne(targetEntity="AnonymousJudge")
     * @ORM\JoinColumn(nullable=true)
     * @var AnonymousJudge
     */
    protected $anonymousJudge;
    
    /**
     * The interpreter is requested by a Person (submitter).
     * 
     * @ORM\ManyToOne(targetEntity="Person")
     * @ORM\JoinColumn(nullable=true)    
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
     * @var Hat
     */
    protected $anonymousSubmitter;
    
    /**
     * @ORM\Column(type="string",length=15,nullable=false,options={"default":""})
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
     * @var ReasonForCancellation
     */
    protected $cancellationReason;
  
    
    /**
     * comments for semi-public consumption
     * 
     * @ORM\Column(type="string",length=600,nullable=false,options={"default":""})
     * @var string
     */
    protected $comments;
    
    /**
     * comments for managers and admins only
     * 
     * @ORM\Column(type="string",length=600,nullable=false,options={"default":""})
     * @var string
     */
    protected $admin_comments;
    
    /**
     * defendant(s) for whom an interpreter is required.
     * 
     * In reality, the DefendantName entity models just that: a name, as opposed 
     * to a person. This is because we expect names to recur, and we recycle them.
     * We usually don't know or care about the actual identity of the defendant 
     * and don't attempt to associate directly a name with a docket number.
     * 
     * @ORM\ManyToMany(targetEntity="DefendantName")
     * @ORM\JoinTable(name="defendants_events",inverseJoinColumns={@ORM\JoinColumn(name="defendant_id", referencedColumnName="id")})
     * @var DefendantName[]
     */
    protected $defendants;
    
    
    
  
    
}
