<?php 
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity  @ORM\Table(name="thing1") */
class Thing1
{

	/**
	 * @var int
	 * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
	 */
	protected $id;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	 protected $shit;

	 /**
	  * @var Thing2
	  * @ORM\OneToOne(targetEntity="Thing2",fetch="EAGER")
	  * @ORM\JoinColumn(name="thing2_id", referencedColumnName="id")
	  * 
	  */
	 protected $thing2;


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
     * Set shit
     *
     * @param string $shit
     *
     * @return Thing1
     */
    public function setShit($shit)
    {
        $this->shit = $shit;

        return $this;
    }

    /**
     * Get shit
     *
     * @return string
     */
    public function getShit()
    {
        return $this->shit;
    }

    /**
     * Set thing2
     *
     * @param \Application\Entity\Thing2 $thing2
     *
     * @return Thing1
     */
    public function setThing2(\Application\Entity\Thing2 $thing2 = null)
    {
        $this->thing2 = $thing2;

        return $this;
    }

    /**
     * Get thing2
     *
     * @return \Application\Entity\Thing2
     */
    public function getThing2()
    {
        return $this->thing2;
    }
}
