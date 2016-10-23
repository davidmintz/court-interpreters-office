<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity representing a reason why something was cancelled.
 *
 * These categories should be set up once and for all at installation time.
 *
 * @ORM\Entity
 * @ORM\Table(name="cancellation_reasons",uniqueConstraints=@ORM\UniqueConstraint(name="unique_cancel_reason",columns={"reason"}))
 */
class ReasonForCancellation
{
    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    protected $id;

    /**
     * @ORM\Column(type="string",length=40,options={"nullable":false})
     *
     * @var string
     */
    protected $reason;

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
     * Set reason.
     *
     * @param string $category
     *
     * @return ReasonForCancellation
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get category.
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    public function __toString()
    {
        return $this->reason;
    }
}
