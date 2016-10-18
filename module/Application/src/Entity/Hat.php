<?php

// module/Application/src/Application/Entity/Hat.php

/**
 * class representing the "hat" a person wears, e.g., 
 * staff interpreter, contract interpreter, AUSA, USPO, 
 * defense attorney, etc
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

//uniqueConstraints={@UniqueConstraint(name="search_idx", columns={"name", "email"})})

/** @ORM\Entity  @ORM\Table(name="hats",uniqueConstraints={@ORM\UniqueConstraint(name="hat_idx",columns={"type"})}) */

class Hat
{

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    protected $id;

    /**
     * @ORM\Column(type="string",length=50,nullable=false)
     * @var string
     */
    protected $type;

    public function __toString() {
        return $this->hat;
    }
    
    /**
     * de facto alias for getType()
     * @return string
     */
    public function getHat() {
        return $this->type;
    }

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
     * Set type
     *
     * @param string $type
     *
     * @return Hat
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
