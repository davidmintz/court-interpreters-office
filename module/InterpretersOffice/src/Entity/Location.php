<?php

/** module/InterpretersOffice/src/Entity/Location.php */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;
use Laminas\Form\Annotation;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity class representing a location where an interpreting event takes place.
 *
 * @todo consider making Courtroom and Courthouse subclasses of Location, considering
 * that only these have a relationship to the Judge entity.
 *
 * example:
 * "SELECT j.lastname AS judge, l.name AS location, p.name courthouse FROM
 * InterpretersOffice\Entity\Location l JOIN l.judges j LEFT JOIN l.parentLocation p"
 *
 * @ORM\Entity  @ORM\Table(name="locations",uniqueConstraints={@ORM\UniqueConstraint(name="unique_name_and_parent",columns={"name","parent_location_id"})})
 * @ORM\Entity(repositoryClass="InterpretersOffice\Entity\Repository\LocationRepository")
 * @Annotation\Name("location")
 */
class Location
{
    /**
     * entity id.
     *
     * @Annotation\Attributes({"type":"hidden"})
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     *
     * @var integer
     */
    protected $id;

    /**
     * name of the location.
     *
     * @Annotation\Attributes({"type":"text","placeholder":"the name of the location","size":36,"class":"form-control","id":"name"})
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
     * @ORM\ManyToOne(targetEntity="Location",fetch="EAGER",inversedBy="childLocations")
     *
     * @var Location
     */
    protected $parentLocation;

    /**
     * comments about this location.
     *
     * @Annotation\Attributes({"class":"form-control","id":"comments","placeholder":"optional comments about this location"})
     * @Annotation\Options({"label":"comments"})
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\AllowEmpty()
     * @Annotation\Required(false)
     * @Annotation\Validator({"name":"StringLength", "options":{"min":2, "max":200,
     *  "messages":{"stringLengthTooShort":"comments must be at least 2 characters long",
     *   "stringLengthTooLong":"comments exceed maximum length of 200 characters"}}})

     * @ORM\Column(type="string",length=200,options={"default":""})
     *
     * @var string
     */
    protected $comments;

    /**
     * whether the location is "active" or not (i.e., archival).
     *
     * @ORM\Column(type="boolean",options={"nullable":false,"default":true})
     * @Annotation\Type("Laminas\Form\Element\Radio")
     * @Annotation\Attributes({"class":"form-control","value":1})
     * @Annotation\Options({"label":"is this location actively in use?",
     *  "value_options" : {"1":"yes","0":"no"}
     * })
     * @Annotation\Validator({"name":"InArray",
     *    "options":{"haystack":{"0","1"},
     *      "messages":{"notInArray":"illegal value: only 0 or 1 are accepted"}}})
     */
    protected $active = true;


    /**
     *
     *
     * @ORM\OneToMany(targetEntity="InterpretersOffice\Entity\Judge",mappedBy="defaultLocation")
     * @var ArrayCollection
     *
     * no setter/getter for this because we will not be managing the Judge's
     * default location from here
     */
    protected $judges;

    /**
     * ArrayCollection related Events
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Event",mappedBy="location")
     */
    protected $events;

    /**
     * ArrayCollection child locations
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Location",mappedBy="parentLocation")
     */
    protected $childLocations;



    /**
     * constructor
     */
    public function __construct()
    {
        $this->judges = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->childLocations = new ArrayCollection();
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
     * @param \InterpretersOffice\Entity\LocationType $type
     *
     * @return Location
     */
    public function setType(\InterpretersOffice\Entity\LocationType $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return \InterpretersOffice\Entity\LocationType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set parentLocation.
     *
     * @param \InterpretersOffice\Entity\Location $parentLocation
     *
     * @return Location
     */
    public function setParentLocation(\InterpretersOffice\Entity\Location $parentLocation = null)
    {
        if ($parentLocation) {
            $this->parentLocation = $parentLocation;
        }

        return $this;
    }

    /**
     * Get parentLocation.
     *
     * @return \InterpretersOffice\Entity\Location
     */
    public function getParentLocation()
    {
        return $this->parentLocation;
    }

    /**
     * set 'active' property.
     *
     * @param bool $active
     *
     * @return \InterpretersOffice\Entity\Location
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * get 'active' property.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * returns a string representation of the entity.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}
