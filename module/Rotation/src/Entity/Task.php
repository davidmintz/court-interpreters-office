<?php
/** module/Rotation/src/Entity/Task.php */

declare(strict_types=1);

namespace InterpretersOffice\Admin\Rotation\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InterpretersOffice\Entity\User;
use DateTime;
use JsonSerializable;

/**
 * Entity class representing a rotating Task
 *
 * @ORM\Entity(repositoryClass="InterpretersOffice\Admin\Rotation\Entity\RotationRepository")
 * @ORM\Table(name="tasks",uniqueConstraints={@ORM\UniqueConstraint(name="unique_name",columns={"name"})})
 * @ORM\HasLifecycleCallbacks
 */
class Task
{

    /**
     * entity id.
     *
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    private $id;

    /**
     * name of the task
     *
     * @ORM\Column(type="string",nullable=false,length=30)
     * @var string
     */
    private $name;

    /**
     * description of the task
     *
     * @ORM\Column(type="string",nullable=false,length=400)
     * @var string
     */
    private $description = '';


    /**
     * permitted values for frequency, duration
     * @var array
     */
    private $values = ['DAY', 'WEEK', 'MONTH'];

    /**
     * frequency
     *
     * How frequently task-assignment recurs. This can be one of:
     * 'DAY', 'WEEK', 'MONTH'
     * @ORM\Column(type="string",nullable=false,length=5)
     * @var string
     */
    private $frequency = 'WEEK';

    /**
     * duration
     *
     * How long the task-assignment lasts. This can be one of:
     * 'DAY', 'WEEK', 'MONTH'
     * @ORM\Column(type="string",nullable=false,length=5)
     * @var string
     */
    private $duration;

    /**
     * day of week
     *
     * @ORM\Column(type="smallint",nullable=true,options={"unsigned":true})
     * @var int
     */
    private $day_of_week;

    /**
     * rotations
     * @ORM\OneToMany(targetEntity="Rotation",mappedBy="task",cascade={"persist"})
     * @var Rotation[]
     */
    private $rotations;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->rotations = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Task
     */
    public function setName($name) : Task
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Task
     */
    public function setDescription($description) : Task
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get duration.
     *
     * @return string
     */
    public function getDuration() : string
    {
        return $this->duration;
    }

    /**
     * Set duration.
     *
     * @param string $description
     * @throws \InvalidArgumentException
     * @return Task
     */
    public function setDuration(string $duration) : Task
    {
        if (! in_array($duration,$this->values)) {
            throw new \InvalidArgumentException(
                "invalid duration $duration, must be one of 'DAY','WEEK', or 'MONTH'");
        }
        $this->duration = $duration;

        return $this;
    }

    /**
     * Sets frequency.
     *
     * @param string $frequency
     * @throws \InvalidArgumentException
     * @return Task
     */
    public function setFrequency(string $frequency) : Task
    {
        if (! in_array($frequency,$this->values)) {
            throw new \InvalidArgumentException(
                "invalid duration $frequency, must be one of 'DAY','WEEK', or 'MONTH'");
        }
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * gets frequency
     *
     * @return string
     */
    public function getFrequency() : string
    {
        return $this->frequency;
    }

    /**
     * gets day_of_week
     *
     * @return int ranging from 1 - 7
     */
    public function getDayOfWeek() : ?int
    {
        return $this->day_of_week;
    }

    /**
     * sets day of week
     * @param  int  $dow
     * @throws \InvalidArgumentException
     * @return Task
     */
    public function setDayOfWeek(int $dow) : Task
    {
        if (! in_array($dow,range(0,6))) {
            throw new \InvalidArgumentException("invalid value for day_of_week: $dow");
        }
        $this->day_of_week = $dow;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }


    /**
     * Add rotation.
     *
     * @param Rotation $rotation
     *
     * @return Task
     */
    public function addRotation(Rotation $rotation) : Task
    {
        $this->rotations[] = $rotation;

        return $this;
    }

    /**
     * Remove rotation.
     *
     * @param Rotation $rotation
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeRotation(Rotation $rotation) : bool
    {
        return $this->rotations->removeElement($rotation);
    }

    /**
     * Get rotations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRotations() : Collection
    {
        return $this->rotations;
    }

}
