<?php
/** module/InterpretersOffice/src/Entity/DocketAnnotation.php */
declare(strict_types=1);

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;
use InterpretersOffice\Entity\DocketAnnotation;

/**
* Docket annotation.
*
* @ORM\Entity(repositoryClass="InterpretersOffice\Entity\Repository\DocketAnnotationRepository")
* @ORM\Table(name="docket_annotations",indexes={@ORM\Index(name="docket_idx", columns={"docket"})})
*
* //ORM\EntityListeners({"InterpretersOffice\Entity\Listener\..."})
*/
class DocketAnnotation
{
    /**
     * entity id.
     *
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    private $id;

    /**
     * docket number
     *
     * @ORM\Column(type="string",length=15,nullable=false)
     * @var string
     */
    private $docket;


    /**
     * comments
     *
     * @ORM\Column(type="string",length=600,nullable=false)
     * @var string
     */
    private $comment;

    /**
     * priority
     * @ORM\Column(type="smallint",nullable=false,options={"unsigned":true})
     * @var int
     */
    private $priority;

    /**
     * User who created this annotation
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false,name="created_by_id")
     */
    private $created_by;

    /**
     * date/time annotation was created.
     *
     * @ORM\Column(type="datetime",nullable=false)
     *
     * @var \DateTime
     */
    private $created;

    /**
     * timestamp of last update.
     *
     * @ORM\Column(type="datetime",nullable=true)
     *
     * @var \DateTime
     */
    private $modified;

    /**
     * last User who updated this annotation
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=true,name="modified_by_id")
     */
    private $modified_by;

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
     * Set docket.
     *
     * @param string $docket
     *
     * @return DocketAnnotation
     */
    public function setDocket(string $docket) : DocketAnnotation
    {
        $this->docket = $docket;

        return $this;
    }

    /**
     * Get docket.
     *
     * @return string
     */
    public function getDocket() : string
    {
        return $this->docket;
    }

    /**
     * Set priority.
     *
     * @param int $priority
     *
     * @return DocketAnnotation
     */
    public function setPriority($priority): DocketAnnotation
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority.
     *
     * @return int
     */
    public function getPriority() : int
    {
        return (int)$this->priority;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return DocketAnnotation
     */
    public function setCreated($created): DocketAnnotation
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated() : DateTime
    {
        return $this->created;
    }

    /**
     * Set modified.
     *
     * @param \DateTime|null $modified
     *
     * @return DocketAnnotation
     */
    public function setModified($modified = null): DocketAnnotation
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified.
     *
     * @return \DateTime|null
     */
    public function getModified() :? DateTime
    {
        return $this->modified;
    }

    /**
     * Set createdBy.
     *
     * @param \InterpretersOffice\Entity\User $createdBy
     *
     * @return DocketAnnotation
     */
    public function setCreatedBy(User $createdBy): DocketAnnotation
    {
        $this->created_by = $createdBy;

        return $this;
    }

    /**
     * Get createdBy.
     *
     * @return \InterpretersOffice\Entity\User
     */
    public function getCreatedBy() : User
    {
        return $this->created_by;
    }

    /**
     * Set modifiedBy.
     *
     * @param \InterpretersOffice\Entity\User|null $modifiedBy
     *
     * @return DocketAnnotation
     */
    public function setModifiedBy(User $modifiedBy = null): DocketAnnotation
    {
        $this->modified_by = $modifiedBy;

        return $this;
    }

    /**
     * Get modifiedBy.
     *
     * @return \InterpretersOffice\Entity\User|null
     */
    public function getModifiedBy() :? User
    {
        return $this->modified_by;
    }

    /**
     * sets comment
     * @param  string           $comment
     * @return DocketAnnotation
     */
    public function setComment(string $comment) : DocketAnnotation
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * gets comment
     * @return string
     */
    public function getComment() : string
    {
        return $this->comment;
    }
}
