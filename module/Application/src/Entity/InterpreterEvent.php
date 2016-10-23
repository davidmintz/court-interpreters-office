<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="interpreters_events", uniqueConstraints={@ORM\UniqueConstraint(name="unique_interp_event",columns={"interpreter_id","event_id"})})
 * @ORM\HasLifecycleCallbacks
 */
class InterpreterEvent
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Interpreter")
     * //ORM\JoinColumn(name="interp_id", referencedColumnName="interp_id")
     *
     * @var Interpreter
     */
    protected $interpreter;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Event",inversedBy="interpretersAssigned")
     * //ORM\JoinColumn(name="event_id", referencedColumnName="event_id")
     *
     * @var Event
     */
    protected $event;

    /**
     * date/time when interpreter was assigned was created.
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

    public function __construct(Event $event = null, Interpreter $interpreter = null)
    {
        $this->interpreter = $interpreter;
        $this->event = $event;
    }

    /**
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
}
