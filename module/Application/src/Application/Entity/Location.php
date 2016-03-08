<?php 

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity  @ORM\Table(name="locations") */

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
	 * 
	 * @ORM\ManyToOne(targetEntity="LocationType")
	 */
	protected $type;

	/**
	 * 
	 * @ORM\ManyToOne(targetEntity="Location")
	 */
	protected $parent_location;

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
    public function setType(\Application\Entity\LocationType $type = null)
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
        $this->parent_location = $parentLocation;

        return $this;
    }

    /**
     * Get parentLocation
     *
     * @return \Application\Entity\Location
     */
    public function getParentLocation()
    {
        return $this->parent_location;
    }
}
