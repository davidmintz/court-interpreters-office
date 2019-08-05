<?php

/** module/InterpretersOffice/src/Entity/JudgeFlavor.php */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity representing a judge "flavor".
 *
 * For federal court, that means either USMJ or USDJ. This should be set up just
 * once at installation time.
 *
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="judge_flavors",uniqueConstraints={@ORM\UniqueConstraint(name="unique_judge_flavor",columns={"flavor"})})
 */
class JudgeFlavor
{
    /**
     * entity id.
     *
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * the "flavor".
     *
     * @ORM\Column(type="string",length=60,options={"nullable":false})
     *
     * @var string
     */
    protected $flavor;

    /**
     * weight, for sorting
     *
     * @ORM\Column(type="integer",options={"nullable":false})
     * @var int
     */
    protected $weight = 0;

    /**
     * returns a string representation of this JudgeFlavor.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getFlavor();
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
     * Get flavor.
     *
     * @return string
     */
    public function getFlavor()
    {
        return $this->flavor;
    }

    /**
     * set flavor.
     *
     * @param string $flavor
     *
     * @return JudgeFlavor
     */
    public function setFlavor($flavor)
    {
        $this->flavor = $flavor;

        return $this;
    }

    /**
     * sets weight
     *
     * @param int $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * gets weight
     */
    public function getWeight()
    {
        return $this->weight;
    }
}
