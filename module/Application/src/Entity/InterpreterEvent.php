<?php
/** module/Application/src/Entity/InterpreterEvent.php */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity representing an interpreter-event.
 * 
 * @ORM\Entity
 * @ORM\Table(name="interpreters_events", uniqueConstraints={@ORM\UniqueConstraint(name="unique_interp_event",columns={"interpreter_id","event_id"})})
 * @ORM\HasLifecycleCallbacks
 */
class InterpreterEvent
{
    /**
     * The Interpreter.
     * 
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Interpreter")
     *
     * @var Interpreter
     */
    protected $interpreter;

    /**
     * The Event.
     * 
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Event",inversedBy="interpretersAssigned")
     * @var Event
     */
    protected $event;

    /**
     * date/time when interpreter was assigned (i.e., when entity was created).
     *
     * @ORM\Column(type="datetime",nullable=false)
     *
     * @var \DateTime
     */
    protected $created;

    /**
     * User who assigned the interpreter.
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     *
     * @var User
     */
    protected $createdBy;
    
    /**
     * constructor
     * 
     * @param Interpreter $interpreter
     * @param Event $event
     */
    public function __construct(Interpreter $interpreter = null,Event $event = null)
    {
        $this->interpreter = $interpreter;
        $this->event = $event;
    }

    /**
     * Automatically sets creation datetime.
     * 
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new \DateTime();
    }

    /**
     * Set interpreter.
     *
     * @param \Application\Entity\Interpreter $interpreter
     *
     * @return InterpreterAssignment
     */
    public function setInterpreter(\Application\Entity\Interpreter $interpreter)
    {
        $this->interpreter = $interpreter;

        return $this;
    }

    /**
     * Get interpreter.
     *
     * @return \Application\Entity\Interpreter
     */
    public function getInterpreter()
    {
        return $this->interpreter;
    }

    /**
     * Set event.
     *
     * @param \Application\Entity\Event $event
     *
     * @return InterpreterAssignment
     */
    public function setEvent(\Application\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event.
     *
     * @return \Application\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return InterpreterAssignment
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
    
    /* ------------------ */
    /**
     * sets createdBy property
     * @param \Application\Entity\User $user
     * @return InterpreterEvent
     */
    public function setCreatedBy(User $user)
    {
        $this->createdBy = $user;
        return $this;
    }
    
    /**
     * gets createdBy property
     * 
     * @return User
     */
    public function sgtCreatedBy()
    {
        
        return $this->createdBy;
    }
}
