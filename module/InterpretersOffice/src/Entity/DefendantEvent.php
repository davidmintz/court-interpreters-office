<?php /** module/InterpretersOffice/src/Entity/DefendantEvent.php */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;
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

    


}
