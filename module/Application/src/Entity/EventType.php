<?php

namespace Application\Entity;
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
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="EventCategory") 
     * @see Application\Entity\EventCategory
     */
    protected $category;

    /**
    * @ORM\Column(type="string",length=60,options={"nullable":false})
    * @var string
    */
    protected $name;

    /**
    * @ORM\Column(type="string",length=60,options={"nullable":false,"default":""})
    * @var string
    */
    protected $comments;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set comments
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
     * Get comments
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set category
     *
     * @param \Application\Entity\EventCategory $category
     *
     * @return EventType
     */
    public function setCategory(\Application\Entity\EventCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Application\Entity\EventCategory
     */
    public function getCategory()
    {
        return $this->category;
    }
}
