<?php
/** module/Rotation/src/Entity/Rotation.php */

declare(strict_types=1);

namespace InterpretersOffice\Admin\Rotation\Entity;


use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Entity class representing the rotation for a task
 *
 * @ORM\Entity(repositoryClass="InterpretersOffice\Admin\Rotation\Entity\RotationRepository")
 * @ORM\Table(name="rotations",uniqueConstraints={@ORM\UniqueConstraint(name="uniq_rotation",columns={"task_id","start_date"})})
 * 
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
     * @ORM\ManyToOne(targetEntity="Task",inversedBy="rotations",cascade={"persist"})
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
     * members of the rotation
     * 
     * @ORM\OneToMany(targetEntity="RotationMember",mappedBy="rotation",cascade={"persist","remove"},orphanRemoval=true)
     * @ORM\OrderBy({"order"="ASC"})
     *
     * @var ArrayCollection
     */
    private $members;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->members = new ArrayCollection();
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


    /**
     * Add member.
     *
     * @param RotationMember $member
     *
     * @return Rotation
     */
    public function addMember(RotationMember $member) : Rotation
    {
        $this->members[] = $member;

        return $this;
    }

    /**
     * Remove member.
     *
     * @param RotationMember $member
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeMember(RotationMember $member) : bool
    {
        return $this->members->removeElement($member);
    }

    /**
     * Get members.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMembers() : Collection
    {
        return $this->members;
    }

    /**
     * adds members
     * 
     * @param Collection $members
     */
    public function addMembers(Collection $members)
    {
        foreach ($members as $m) {
            $m->setRotation($this);
            $this->members->add($m);
        }
    }

    /**
     * removes members
     * 
     * @param Collection $members
     */
    public function removeMembers(Collection $members)
    {
        foreach ($members as $m) {
            $m->setRotation(null);
            $this->members->removeElement($m);
        }
    }
}
