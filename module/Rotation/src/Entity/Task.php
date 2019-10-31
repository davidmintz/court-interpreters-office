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
     * duration
     *
     * How long the task-assignment lasts. This can be one of:
     * 'DAY', 'WEEK', 'MONTH'
     * @ORM\Column(type="string",nullable=false,length=5)
     * @var string
     */
    private $duration;

    /**
     * rotations
     * @ORM\OneToMany(targetEntity="Rotation",mappedBy="task")
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
     *
     * @return Task
     */
    public function setDuration($duration) : Task
    {
        $this->duration = $duration;

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
