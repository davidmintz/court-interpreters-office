<?php
/** module/Rotation/src/Entity/Substitution.php */

declare(strict_types=1);

namespace InterpretersOffice\Admin\Rotation\Entity;

use Doctrine\ORM\Mapping as ORM;
use InterpretersOffice\Entity\Person;
use DateTime;

/**
 * Entity class representing a Person substitution for a Task
 *
 * @ORM\Entity(repositoryClass="InterpretersOffice\Admin\Rotation\Entity\SubstitutionRepository")
 * @ORM\Table(name="rotation_substitutions",
 * uniqueConstraints={@ORM\UniqueConstraint(name="subst_idx", columns={"date","rotation_id","duration"})})
 * @ORM\HasLifecycleCallbacks
 */

class Substitution
{
    /**
     * entity id.
     *
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    private $id;


    /**
     * (start) date of substitution
     * @ORM\Column(type="date",nullable=false)
     * @var DateTime
     */
    private $date;

    /**
     * the Rotation whose default Person is substituted
     *
     * @ORM\ManyToOne(targetEntity="Rotation")
     * @ORM\JoinColumn(nullable=false)
     * @var Rotation
     */
    private $rotation;

    /**
     * duration
     *
     * How long the task-assignment substitution lasts. This can be one of:
     * 'DAY', 'WEEK', 'MONTH'
     * @ORM\Column(type="string",nullable=false,length=5)
     * @var string
     */
    private $duration = 'DAY';

    /**
     *
     * @ORM\ManyToOne(targetEntity="InterpretersOffice\Entity\Person")
     * @var Person
     */
    private $person;


    public function __construct(Rotation $rotation)
    {
        $this->rotation = $rotation;
    }

    /**
     *
     * @ORM\prePersist
     */
    public function prePersist()
    {
        $this->checkDate();
    }
    /**
     *
     * @ORM\preUpdate
     */
    public function preUpdate()
    {
        $this->checkDate();
    }

    /**
     * Tests whether $this->date is a monday when duration is a week, and
     * ensures $this->date does not precede the Rotation start date.
     *
     * We could silently reset the date instead of throwing an exception,
     * but the thinking here is that we don't know the intentions, or which
     * field might be wrong, so we don't assume.
     *
     * @throws \RuntimeException
     * @return void
     */
    public function checkDate()
    {
        if ('WEEK' == $this->getDuration()) {
            $date = $this->getDate();
            if ($date && $date->format('N') != 1) {
                throw new \RuntimeException(
                    'For substitutions lasting a week, the date must be a Monday'
                );
            }
        }
        // maybe...
        $start = $this->getRotation()->getStartDate();
        if ($this->date < $start) {
            throw new \RuntimeException(
                'The date of the substition cannot precede the start date of the rotation'
            );
        }
    }

    /**
     * sets Rotation
     *
     * @param  Rotation
     * @return Substitution
     */
    public function setRotation(Rotation $rotation) : Substitution
    {
        $this->rotation = $rotation;

        return $this;
    }

    /**
     * Get Rotation.
     *
     * @return Rotation
     */
    public function getRotation() : Rotation
    {
        return $this->rotation;
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
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Substitution
     */
    public function setDate($date) : Substitution
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate() : DateTime
    {
        return $this->date;
    }

    /**
     * Set duration.
     *
     * @param string $duration
     *
     * @return Substitution
     */
    public function setDuration($duration) : Substitution
    {
        $this->duration = $duration;

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
     * Set person.
     *
     * @param Person $person
     *
     * @return Substitution
     */
    public function setPerson(Person $person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * Get person.
     *
     * @return Person
     */
    public function getPerson() : Person
    {
        return $this->person;
    }

}
