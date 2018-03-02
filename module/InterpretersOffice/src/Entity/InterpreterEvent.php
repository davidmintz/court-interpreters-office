<?php

/** module/InterpretersOffice/src/Entity/InterpreterEvent.php */

namespace InterpretersOffice\Entity;

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
     * @ORM\ManyToOne(targetEntity="Event",inversedBy="interpreterEvents",cascade="remove")
     *
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
     * @ORM\JoinColumn(nullable=false,name="created_by_id")
     *
     * @var User
     */
    protected $createdBy;

    /**
     * current data and time
     *
     * @var \DateTime
     */
    protected $now;

    /**
     * constructor.
     *
     * @param Interpreter $interpreter
     * @param Event       $event
     */
    public function __construct(Interpreter $interpreter = null, Event $event = null)
    {
        $this->interpreter = $interpreter;
        $this->event = $event;
        $this->now = new \DateTime();
    }

    /**
     * Automatically sets creation datetime.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {

        $this->created = $this->now;
         return;
        $event = $this->getEvent();
        if ($event->getModified() != $this->now) {
            $event->setModified($this->now);
        }
    }

    /**
     * Set interpreter.
     *
     * @param \InterpretersOffice\Entity\Interpreter $interpreter
     *
     * @return InterpreterAssignment
     */
    public function setInterpreter(\InterpretersOffice\Entity\Interpreter $interpreter)
    {
        $this->interpreter = $interpreter;

        return $this;
    }

    /**
     * Get interpreter.
     *
     * @return \InterpretersOffice\Entity\Interpreter
     */
    public function getInterpreter()
    {
        return $this->interpreter;
    }

    /**
     * Set event.
     *
     * @param \InterpretersOffice\Entity\Event $event
     *
     * @return InterpreterAssignment
     */
    public function setEvent(\InterpretersOffice\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event.
     *
     * @return \InterpretersOffice\Entity\Event
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
     * sets createdBy property.
     *
     * @param \InterpretersOffice\Entity\User $user
     *
     * @return InterpreterEvent
     */
    public function setCreatedBy(User $user)
    {
        $this->createdBy = $user;

        return $this;
    }

    /**
     * gets createdBy property.
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }
}
