<?php

/** module/InterpretersOffice/src/Entity/EventType.php */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity representing a type of court interpreter proceeding or event.
 *
 * Examples include: attorney-client interview, pre-trial conference,
 * plea, sentence, probation pre-sentence interview, etc.
 *
 * @ORM\Entity  @ORM\Table(name="event_types",uniqueConstraints={@ORM\UniqueConstraint(name="unique_event_type",columns={"name"})})

 */
class EventType
{
    /**
     * entity id.
     *
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    protected $id;

    /**
     * the "category" of this event-type, e.g., in-court, out-of-court.
     *
     * @ORM\ManyToOne(targetEntity="EventCategory")
     * @ORM\JoinColumn(nullable=false)
     *
     * @see InterpretersOffice\Entity\EventCategory
     */
    protected $category;

    /**
     * the name of this event-type (e.g., plea, sentencing, etc).
     *
     * @ORM\Column(type="string",length=60,options={"nullable":false})
     *
     * @var string
     */
    protected $name;

    /**
     * comments.
     *
     * @ORM\Column(type="string",length=60,options={"nullable":false,"default":""})
     *
     * @var string
     */
    protected $comments;

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
     * Set name.
     *
     * @param string $name
     *
     * @return EventType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set comments.
     *
     * @param string $comments
     *
     * @return EventType
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
     * Set category.
     *
     * @param \InterpretersOffice\Entity\EventCategory $category
     *
     * @return EventType
     */
    public function setCategory(\InterpretersOffice\Entity\EventCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category.
     *
     * @return \InterpretersOffice\Entity\EventCategory
     */
    public function getCategory()
    {
        return $this->category;
    }
}
