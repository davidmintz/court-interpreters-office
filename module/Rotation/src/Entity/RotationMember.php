<?php
/** module/Rotation/src/Entity/RotationMember.php */


namespace InterpretersOffice\Admin\Rotation\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InterpretersOffice\Entity\Person;
use DateTime;
use JsonSerializable;

/**
 * Entity class representing a member of a rotating task
 *
 * @ORM\Entity
 * @ORM\Table(name="task_rotation_members")})
 * //ORM\HasLifecycleCallbacks
 */

class RotationMember
{
    /**
     * rotation of which this entity is a member
     *
     * @ORM\ManyToOne(targetEntity="Rotation",inversedBy="members") @ORM\Id
     *
     * @var Rotation
     */
    private $rotation;

    /**
     * @ORM\ManyToOne(targetEntity="InterpretersOffice\Entity\Person") @ORM\Id
     * 
     * @var Person
     */
    private $person;

    /**
     * position in the batting order
     *
     * @ORM\Column(type="smallint",name="rotation_order",options={"nullable":false,"unsigned":true})
     * @var int
     */
    private $order;

    /**
     * Set order.
     *
     * @param int $order
     *
     * @return RotationMember
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order.
     *
     * @return int
     */
    public function getOrder() : int
    {
        return $this->order;
    }

    /**
     * Set rotation.
     *
     * @param Rotation $rotation
     *
     * @return RotationMember
     */
    public function setRotation(Rotation $rotation = null) : RotationMember
    {
        $this->rotation = $rotation;

        return $this;
    }

    /**
     * Get rotation.
     *
     * @return Rotation
     */
    public function getRotation()
    {
        return $this->rotation;
    }

    /**
     * Set person.
     *
     * @return Person
     *
     * @return RotationMember
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
    public function getPerson()
    {
        return $this->person;
    }

}
