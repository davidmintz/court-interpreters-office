<?php 

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity  @ORM\Table(name="languages") */

class Language 
{

	/**
	 * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string",length=50,nullable=false)
	 * @var string
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string",length=200,nullable=false)
	 * @var string
 	 */
	protected $comments;


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
     * Set name
     *
     * @param string $name
     *
     * @return Language
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set comments
     *
     * @param string $comments
     *
     * @return Language
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }
}
