<?php
/**
 * module/InterpretersOffice/src/Entity/EventCategory.php.
 */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity representing an event "category," e.g., in-court, out-of-court.
 *
 * These categories should be set up once and for all at installation time.
 *
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="event_categories",uniqueConstraints=@ORM\UniqueConstraint(name="unique_event_category",columns={"category"}))
 */
class EventCategory
{
    /**
     * category id.
     *
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    protected $id;

    /**
     * the category.
     *
     * @ORM\Column(type="string",length=20,options={"nullable":false})
     *
     * @var string
     */
    protected $category;

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
     * Set category.
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
     * Get category.
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * returns string representation
     */
    public function __toString()
    {
        return $this->category;
    }
}
