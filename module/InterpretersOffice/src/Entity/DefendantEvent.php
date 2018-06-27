<?php /** module/InterpretersOffice/src/Entity/DefendantEvent.php */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity representing an defendant-event.
 *
 * @ORM\Entity
 * @ORM\Table(name="defendants_events", uniqueConstraints={@ORM\UniqueConstraint(name="unique_defendant_event",columns={"defendant_id","event_id"})})
 */
class DefendantEvent
{
    /**
     * The defendant (name).
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Defendant",inversedBy="defendantEvents") //,inversedBy="defendantEvents"
     * @ORM\JoinColumn(name="defendant_id")
     *
     * @var Defendant
     */
    protected $defendant;

    /**
     * The Event.
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Event",inversedBy="defendantEvents",cascade="remove")
     *
     * @var Event
     */
    protected $event;

    /**
     * constructor
     * @param Defendant $deftName
     * @param Event $event
     */
    public function __construct(Defendant $deftName = null, Event $event = null)
    {
        $this->defendant = $deftName;
        $this->event = $event;
    }

    /**
     * Set event.
     *
     * @param \InterpretersOffice\Entity\Event $event
     *
     * @return DefendantEvent
     */
    public function setEvent(Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event.
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * sets Defendant
     *
     * @param Defendant $defendant
     * @return DefendantEvent
     */
    public function setDefendant(Defendant $defendant)
    {
        $this->defendant  = $defendant;

        return $this;
    }

    /**
     * gets Defendant
     *
     * @return Defendant
     */
    public function getDefendant()
    {
        return $this->defendant;
    }

    /**
     * toString
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getDefendant()->getId();
    }
}
