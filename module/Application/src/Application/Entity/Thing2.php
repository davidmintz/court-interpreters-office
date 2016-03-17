<?php 
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity  @ORM\Table(name="thing2") */
class Thing2
{

	/**
	 * @var int
	 * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
	 */
	protected $id;

	/**
	 * @var string
	 * @ORM\Column(type="string",name="other_shit")
	 */
	 protected $otherShit;

	 


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
     * Set otherShit
     *
     * @param string $otherShit
     *
     * @return Thing2
     */
    public function setOtherShit($otherShit)
    {
        $this->otherShit = $otherShit;

        return $this;
    }

    /**
     * Get otherShit
     *
     * @return string
     */
    public function getOtherShit()
    {
        return $this->otherShit;
    }
}
