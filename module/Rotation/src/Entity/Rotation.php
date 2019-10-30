<?php
/** module/Rotation/src/Entity/Rotation.php */

declare(strict_types=1);

namespace InterpretersOffice\Admin\Rotation\Entity;


use Doctrine\ORM\Mapping as ORM;
use InterpretersOffice\Entity\User;
use DateTime;
use JsonSerializable;

/**
 * Entity class representing MOTD
 *
 * @ORM\Entity(repositoryClass="InterpretersOffice\Admin\Rotation\Entity\RotationRepository")
 * @ORM\Table(name="rotations")
 * //ORM\HasLifecycleCallbacks
 */
class Rotation
{
    /**
     * entity id.
     *
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    private $id;

    /**
     * task
     *
     * @ORM\ManyToOne(targetEntity="Task",inversedBy="rotations")
     * @var Task
     */
    private $task;

    /**
     * date the rotation begins
     * @var DateTime
     *
     * @ORM\Column(type="date",nullable=false)
     */
    private $start_date;

    /**
     * JSON array of person_ids
     *
     * order is significant
     * @ORM\Column(type="string",nullable=false,length=600)
     * @var string
     */
    private $rotation;


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
     * Set startDate.
     *
     * @param \DateTime $startDate
     *
     * @return Rotation
     */
    public function setStartDate($startDate) : Rotation
    {
        $this->start_date = $startDate;

        return $this;
    }

    /**
     * Get startDate.
     *
     * @return \DateTime
     */
    public function getStartDate() : DateTime
    {
        return $this->start_date;
    }

    /**
     * Set rotation.
     *
     * @param string $rotation
     *
     * @return Rotation
     */
    public function setRotation($rotation) : Rotation
    {
        $this->rotation = $rotation;

        return $this;
    }

    /**
     * Get rotation.
     *
     * @return string
     */
    public function getRotation() : string
    {
        return $this->rotation;
    }

    /**
     * Set task.
     *
     * @param Task $task
     *
     * @return Rotation
     */
    public function setTask(Task $task) : Rotation
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get task.
     *
     * @return Task
     */
    public function getTask() : Task
    {
        return $this->task;
    }
}
