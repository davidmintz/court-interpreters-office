<?php

namespace Application\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entity class representing a date that the court is closed.
 * 
 * The CourtClosing is used for computing the difference between business dates.
 * It could also be used for disabling certain dates on calendar controls or for
 * date input validation.The Court is closed mostly for official holidays but 
 * occasionally on ad-hoc occasions like blizzards and terror attacks.
 * 
 * @see Application\Entity\Holiday
 * @ORM\Entity
 * @ORM\Table(name="court_closings")
 */
class CourtClosing {
    
    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="date")
     * @var string
     */
    protected $date;

    /**
     * @var Holiday
     *
     * @ORM\ManyToOne(targetEntity="Application\Entity\Holiday")
     * @ORM\JoinColumn(name="holiday_id", referencedColumnName="id",nullable=true)
     */
    protected $holiday;

    /**
     * @var string
     * @ORM\Column(type="string",length=75,nullable=true)
     */
    protected $description_other;


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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return CourtClosing
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set descriptionOther
     *
     * @param string $descriptionOther
     *
     * @return CourtClosing
     */
    public function setDescriptionOther($descriptionOther)
    {
        $this->description_other = $descriptionOther;

        return $this;
    }

    /**
     * Get descriptionOther
     *
     * @return string
     */
    public function getDescriptionOther()
    {
        return $this->description_other;
    }

    /**
     * Set holiday
     *
     * @param \Application\Entity\Holiday $holiday
     *
     * @return CourtClosing
     */
    public function setHoliday(\Application\Entity\Holiday $holiday = null)
    {
        $this->holiday = $holiday;

        return $this;
    }

    /**
     * Get holiday
     *
     * @return \Application\Entity\Holiday
     */
    public function getHoliday()
    {
        return $this->holiday;
    }
}



