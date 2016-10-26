<?php
/** module/Application/src/Entity/LocationType.php */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/** 
 * Entity class representing a type of location where interpreter events occur.
 * 
 * Examples: courtroom, courthouse, jail.
 * 
 * @see Application\Entity\Locations
 * @ORM\Entity  @ORM\Table(name="location_types",uniqueConstraints={@ORM\UniqueConstraint(name="unique_type",columns={"type"})}) 
 */
class LocationType
{
    /**
     * location-type id
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    protected $id;

    /**
     * type of location
     * 
     * @ORM\Column(type="string",length=60,nullable=false)
     *
     * @var string
     */
    protected $type;

    /**
     * comments describing the location type.
     * 
     * @ORM\Column(type="string",length=200,nullable=false,options={"default":""})
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
     * Set type.
     *
     * @param string $type
     *
     * @return LocationType
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set comments.
     *
     * @param string $comments
     *
     * @return LocationType
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
}
