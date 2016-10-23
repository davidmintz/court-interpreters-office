<?php 

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
* Entity class representing a location where an interpreting event takes place.
*
* @todo add aboolean property "active" for retiring (hiding) locations no longer in use 
* 
* @ORM\Entity  @ORM\Table(name="locations",uniqueConstraints={@ORM\UniqueConstraint(name="unique_name_and_parent",columns={"name","parent_location_id"})}) 
*/

class Location
{

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    protected $id;

    /**
     * @ORM\Column(type="string",length=60,options={"nullable":false})
     * @var string
     */
    protected $name;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="LocationType")
     * @var LocationType;
     */
    protected $type;

    /**
     * @ORM\JoinColumn(name="parent_location_id",nullable=true)
     * @ORM\ManyToOne(targetEntity="Location")
     */
    protected $parentLocation;

    /**
     * @ORM\Column(type="string",length=200,options={"default":""})
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
     * @return Location
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
     * @return Location
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
     * Set type
     *
     * @param \Application\Entity\LocationType $type
     *
     * @return Location
     */
    public function setType(\Application\Entity\LocationType $type )
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \Application\Entity\LocationType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set parentLocation
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
     * Get parentLocation
     *
     * @return \Application\Entity\Location
     */
    public function getParentLocation()
    {
        return $this->parentLocation;
    }
}
