<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity representing a Judge.
 *
 * Judge inherits from Person.
 *
 * @see Application\Entity\Person
 *
 * @ORM\Entity @ORM\Table(name="judges")
 */
class Judge extends Person
{
    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     *
     * @var int
     */
    protected $id;

    /**
     * A judge has a default Location, i.e., a courtroom.
     *
     * @ORM\JoinColumn(name="default_location_id",nullable=true)
     * @ORM\ManyToOne(targetEntity="Location")
     *
     * @var Location
     */
    protected $defaultLocation;

    /**
     * A Judge has a JudgeFlavor, e.g., "USDJ" in US District Courts.
     *
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="JudgeFlavor")
     */
    protected $flavor;

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
     * Set defaultLocation.
     *
     * @param Location $defaultLocation
     *
     * @return Judge
     */
    public function setDefaultLocation(Location $defaultLocation)
    {
        $this->defaultLocation = $defaultLocation;

        return $this;
    }

    /**
     * Get defaultLocation.
     *
     * @return Location
     */
    public function getDefaultLocation()
    {
        return $this->defaultLocation;
    }

    /**
     * Set flavor.
     *
     * @param JudgeFlavor $flavor
     *
     * @return Judge
     */
    public function setFlavor(JudgeFlavor $flavor)
    {
        $this->flavor = $flavor;

        return $this;
    }

    /**
     * Get flavor.
     *
     * @return JudgeFlavor
     */
    public function getFlavor()
    {
        return $this->flavor;
    }
}
