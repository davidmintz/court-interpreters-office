<?php

/** module/Application/src/Entity/Location.php */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 * Entity class representing a location where an interpreting event takes place.
 *
 * @todo add aboolean property "active" for retiring (hiding) locations no longer in use
 *
 *
 * @ORM\Entity  @ORM\Table(name="locations",uniqueConstraints={@ORM\UniqueConstraint(name="unique_name_and_parent",columns={"name","parent_location_id"})})
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\LocationRepository")
 * @Annotation\Name("location")
 */
class Location
{
    /**
     * entity id.
     * @Annotation\Attributes({"type":"hidden"})
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    protected $id;

    /**
     * name of the location.
     *
     * @Annotation\Attributes({"type":"text","placeholder":"the name of the location","size":36,"class":"form-control","id":"id"})
     * @Annotation\Options({"label":"name"})
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Validator({"name":"NotEmpty",
     *  "break_chain_on_failure": true,
     *  "options":{"messages":{"isEmpty":"location name is required"}
     *  }})
     * @Annotation\Validator({"name":"StringLength", "options":{"min":2, "max":50,
     *  "messages":{"stringLengthTooShort":"language name must be at least 2 characters long",
     *   "stringLengthTooLong":"language name exceeds maximum length of 60 characters"}}})
     *
     * @ORM\Column(type="string",length=60,options={"nullable":false})
     *
     * @var string
     */
    protected $name;

    /**
     * the LocationType of this Location.
     *
     * @ORM\JoinColumn(nullable=false)
     * // this inversedBy is experimental!
     * @ORM\ManyToOne(targetEntity="LocationType",inversedBy="locations")
     *
     * @var LocationType;
     */
    protected $type;

    /**
     * the "parent" location of this location.
     *
     * Our locations data model supports one level of nesting. A courtroom is in
     * a courthouse, so a location of type courtroom has a parent of type
     * courthouse. The parent's parent is simply null. This is implemented as
     * a self-referencing Many-To-One relationship.
     *
     * @ORM\JoinColumn(name="parent_location_id",nullable=true)
     * @ORM\ManyToOne(targetEntity="Location")
     */
    protected $parentLocation;

    /**
     * comments about this location.
     * @Annotation\Attributes({"class":"form-control","placeholder":"optional comments about this location"})
     * @Annotation\Options({"label":"comments"})
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\AllowEmpty()
     * @Annotation\Required(false)
     * @Annotation\Validator({"name":"StringLength", "options":{"min":2, "max":50,
     *  "messages":{"stringLengthTooShort":"comments must be at least 2 characters long",
     *   "stringLengthTooLong":"language name exceeds maximum length of 200 characters"}}})

     * @ORM\Column(type="string",length=200,options={"default":""})
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
     * @return Location
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
     * @return Location
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
     * Set type.
     *
     * @param \Application\Entity\LocationType $type
     *
     * @return Location
     */
    public function setType(\Application\Entity\LocationType $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return \Application\Entity\LocationType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set parentLocation.
     *
     * @param \Application\Entity\Location $parentLocation
     *
     * @return Location
     */
    public function setParentLocation(\Application\Entity\Location $parentLocation = null)
    {
        if ($parentLocation) {
            $this->parentLocation = $parentLocation;
        }

        return $this;
    }

    /**
     * Get parentLocation.
     *
     * @return \Application\Entity\Location
     */
    public function getParentLocation()
    {
        return $this->parentLocation;
    }
}
