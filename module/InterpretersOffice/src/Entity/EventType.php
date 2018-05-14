<?php

/** module/InterpretersOffice/src/Entity/EventType.php */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity representing a type of court interpreter proceeding or event.
 *
 * Examples include: attorney-client interview, pre-trial conference,
 * plea, sentence, probation pre-sentence interview, etc.
 *
 * @ORM\Entity(repositoryClass="InterpretersOffice\Entity\Repository\EventTypeRepository")
 * @ORM\Table(name="event_types",uniqueConstraints={@ORM\UniqueConstraint(name="unique_event_type",columns={"name"})})
 *
 * @Annotation\Name("event-type")
 */
class EventType
{
    /**
     * entity id.
     *
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     * @Annotation\Attributes({"type":"hidden"})
     */
    protected $id;

    /**
     * the "category" of this event-type, e.g., in-court, out-of-court.
     *
     * @ORM\ManyToOne(targetEntity="EventCategory")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Annotation\Type("Zend\Form\Element\Select")
     * Annotation\Required({"required":"true" })
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Options({"label":"category",
     *                      "value_options" : {"":"select category","1":"in","2":"out","3":"not applicable"}})
     * @Annotation\Validator({"name":"NotEmpty",
     *  "break_chain_on_failure": true,
     *  "options":{"messages":{"isEmpty":"event-type category is required"}
     *  }})
     * @Annotation\Validator({"name":"InArray",
     *                        "options":{"haystack":{"","1","2","3"},
     *                              "messages":{"notInArray":"invalid category"}}})
     * @Annotation\Attributes({"value":"0","class":"form-control"})
     *
     * @see InterpretersOffice\Entity\EventCategory
     */
    protected $category;

    /**
     * the name of this event-type (e.g., plea, sentencing, etc).
     *
     * @ORM\Column(type="string",length=60,options={"nullable":false})
     * @Annotation\Attributes({"type":"text","placeholder":"the name of this event-type","size":36,"class":"form-control","id":"name"})
     * @Annotation\Options({"label":"name"})
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Validator({"name":"NotEmpty",
     *  "break_chain_on_failure": true,
     *  "options":{"messages":{"isEmpty":"event-type name is required"}
     *  }})
     * @Annotation\Validator({"name":"StringLength", "options":{"min":2, "max":60,
     *  "messages":{"stringLengthTooShort":"event type's name must be at least 2 characters long",
     *   "stringLengthTooLong":"name exceeds maximum length of 60 characters"}}})
     *
     * @var string
     */
    protected $name;

    /**
     * comments.
     *
     * @Annotation\Attributes({"class":"form-control",
     * "placeholder":"optional comments about this event-type","type":"textarea",
     * "cols":36,"rows":4,})
     * @Annotation\Options({"label":"comments"})
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\AllowEmpty()
     * @Annotation\Required(false)
     * @Annotation\Validator({"name":"StringLength", "options":{"min":2, "max":150,
     *  "messages":{"stringLengthTooShort":"comments must be at least 2 characters long",
     *   "stringLengthTooLong":"comments exceed maximum length of 150 characters"}}})

     * @ORM\Column(type="string",length=150,options={"nullable":false,"default":""})
     *
     * @var string
     */
    protected $comments;



    /**
     * ArrayCollection related Events
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Event",mappedBy="eventType")
     */
    protected $events;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->events = new ArrayCollection();
    }    

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
    /**
     * returns string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
