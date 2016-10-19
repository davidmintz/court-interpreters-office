<?php

namespace Application\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 * Entity representing a category of anonymous submitter of requests for an interpreter.  
 *
 * An assumption of this application is that you will usually want to record the identities
 * of the people who request interpreting services, but there may be exceptions.
 * These are the departments/classifications of people whom the application will not 
 * require the user to identify personally. 
 * 
 * @ORM\Entity
 * @ORM\Table(name="anonymous_hats",uniqueConstraints={@ORM\UniqueConstraint(name="unique_anon_hat",columns={"name"})})
 * 
 */
class AnonymousHat 
{

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    protected $id;

    /**
     * @ORM\Column(type="string",length=40,nullable=false)
     * @var string
     */
    protected $name;
	
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
     * @return AnonymousHat
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
     * returns string representation
     * @return string
     */
    public function __toString() {
        return $this->name;
    }

}
