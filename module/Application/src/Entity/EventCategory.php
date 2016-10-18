<?php

namespace Application\Entity;


use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity  @ORM\Table(name="event_categories",uniqueConstraints=@ORM\UniqueConstraint(name="unique_event_category",columns={"category"})) */

class EventCategory
{

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    protected $id;

    /**
     * 
     * @ORM\Column(type="string",length=20,options={"nullable":false})
     * @var string
     */
    protected $category;



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
     * Set category
     *
     * @param string $category
     *
     * @return EventCategory
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }
}
