<?php

/** module/InterpretersOffice/src/Entity/Judge.php */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity representing a Judge.
 *
 * Judge inherits from Person.
 *
 * @see InterpretersOffice\Entity\Person
 *
 * @ORM\Entity(repositoryClass="InterpretersOffice\Entity\Repository\JudgeRepository")
 * @ORM\Table(name="judges")
 * @ORM\HasLifecycleCallbacks
 */
class Judge extends Person
{
    /**
     * entity id.
     *
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     *
     * @var int
     */
    protected $id;

    /**
     * A judge has a default Location, i.e., a courtroom.
     *
     * @ORM\JoinColumn(name="default_location_id",nullable=true)
     * @ORM\ManyToOne(targetEntity="Location",inversedBy="judges")
     *
     * @var Location
     */
    protected $defaultLocation;

    /**
     * A Judge has a JudgeFlavor, e.g., "USDJ" in US District Courts.
     *
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="JudgeFlavor",fetch="EAGER")
     */
    protected $flavor;

    /**
     * ArrayCollection related Events
     *
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Event",mappedBy="judge")
     */
    protected $events;

    /**
     * constructor
     *
     * @param Hat $hat
     */
    public function __construct(Hat $hat)
    {
        $this->setHat($hat);
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

    /**
     * lifecycle callback to prevent incorrect hat and location-type on update event.
     *
     * proxies to onPrePersist()
     *
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        return $this->onPrePersist();
    }

    /**
     * lifecycle callback to prevent incorrect hat and location-type.on persist event.
     *
     * @ORM\PrePersist
     *
     * @throws \RuntimeException
     */
    public function onPrePersist()
    {
        if ((string) $this->getHat() !== 'Judge') {
            throw new \RuntimeException(
                'Judge entity must have Hat type "Judge"'
            );
            //$this->setHat(new Hat("Judge"));
        }

        if ($this->getDefaultLocation() !== null) {
            if (! in_array(
                (string) $this->getDefaultLocation()->getType(),
                ['courtroom', 'courthouse']
            )) {
                $what = (string) $this->getDefaultLocation()->getType();
                throw new \RuntimeException(
                    sprintf(
                    'Judge entity must have default location of type "courtroom" or "courthouse", got ""%s"
                    for default location %s (id: %d)',
                    $what, (string)$this->getDefaultLocation(),
                    $this->getDefaultLocation()->getId()
                    )

                );
            }
        }
    }
    /**
     * returns a string representation of the entity.
     */
    public function __toString()
    {
        $string = $this->getLastname().', ';
        $string .= $this->getFirstname();
        $middle = $this->getMiddlename();
        if ($middle) {
            $string .= " $middle";
        }
        $string .= ', '.(string) $this->getFlavor();

        return $string;
    }
}
