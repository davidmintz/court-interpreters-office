<?php
namespace Application\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entity representing a category of anonymous/generic judge
 *
 * Events typically have a related Judge entity. A Judge is a Person with attributes
 * like last name, firstname. But some events have a "generic" judge, e.g., the 
 * Magistrate who happens to be on duty when the event takes place. There are also 
 * occasionally "not applicable" cases, such as when some agency or department 
 * requests a document translation for general use, rather than on behalf of a 
 * particular judge.
 * 
 * @ORM\Entity
 * @ORM\Table(name="anonymous_judges",uniqueConstraints={@ORM\UniqueConstraint(name="unique_anon_judge",columns={"name"})})
 * 
 */
class AnonymousJudge {
    
     /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    protected $id;

    /**
     * @ORM\Column(type="string",length=50,nullable=false)
     * @var string
     */
    protected $name;
    
    /**
     * @ORM\Column(name="default_location_id",nullable=true)
     * @ORM\ManyToOne(targetEntity="Location") 
     */
    protected $defaultLocation;

    /**
    * returns string representation of AnonymousJudge entity
    */
    public function __toString() {
        return $this->name;
    }
    
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
     * @param string $type
     *
     * @return AnonymousJudge
     */
    public function setName($type)
    {
        $this->name = $type;

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
     * Set defaultLocation
     *
     * @param Location $defaultLocation
     *
     * @return AnonymousJudge
     */
    public function setDefaultLocation(Location $defaultLocation)
    {
        $this->defaultLocation = $defaultLocation;

        return $this;
    }

    /**
     * Get defaultLocation
     *
     * @return string
     */
    public function getDefaultLocation()
    {
        return $this->defaultLocation;
    }
}
