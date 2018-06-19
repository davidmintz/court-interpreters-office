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
     * The defendantName.
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="DefendantName")
     *
     * @var DefendantName
     */
    protected $defendant;

    /**
     * The Event.
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Event")
     *
     * @var Event
     */
    protected $event;

    /**
     * constructor
     * @param DefendantName $deftName
     * @param Event $event
     */
    public function __construct(DefendantName $deftName = null, Event $event = null)
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
     * sets DefendantName
     *
     * @param DefendantName $defendant
     * @return DefendantEvent
     */
    public function setDefendantName(DefendantName $defendant = null)
    {
        $this->defendant  = $defendant;

        return $this;
    }

    /**
     * gets DefendantName
     *
     * @return DefendantName
     */
    public function getDefendantName()
    {
        return $this->defendant;
    }
}
