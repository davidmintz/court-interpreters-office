<?php /** module/InterpretersOffice/src/Entity/DefendantEvent.php */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;
use InterpretersOffice\Entity\DefendantName;
/**
 * Entity representing an defendant-event.
 *
 * @ORM\Entity
 * @ORM\Table(name="defendants_events", uniqueConstraints={@ORM\UniqueConstraint(name="unique_defendant_event",columns={"defendant_id","event_id"})})
 *
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

    public function __construct()
    {

    }

    /**
     * Set event.
     *
     * @param \InterpretersOffice\Entity\Event $event
     *
     * @return InterpreterEvent
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
    public function setDefendantName(DefendantName $defendant)
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
