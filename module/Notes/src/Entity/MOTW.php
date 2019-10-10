<?php
/** MOTD.php */

declare(strict_types=1);

namespace InterpretersOffice\Admin\Notes\Entity;

use Doctrine\ORM\Mapping as ORM;
use InterpretersOffice\Entity\User;
use DateTime;

/**
 * Entity class representing MOTW a/k/a Message Of The Week
 *
 * @ORM\Entity(repositoryClass="InterpretersOffice\Admin\Notes\Entity\MOTDRepository")
 * @ORM\Table(name="motw",uniqueConstraints={@ORM\UniqueConstraint(name="week_idx",columns={"week_of"})})
 * @ORM\HasLifecycleCallbacks
 */
class MOTW implements \JsonSerializable, NoteInterface
{

    /**
     * entity id.
     *
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    private $id;

    /**
     * date
     *
     * @ORM\Column(type="date",nullable=false)
     */
    private $week_of;

    /**
     * content
     *
     * @var @ORM\Column(type="string",nullable=false,length=2000)
     */
    private $content;

    /**
    * timestamp of motw creation.
    *
    * @ORM\Column(type="datetime",nullable=false)
    *
    * @var \DateTime
    */
    private $created;

    /**
     * last User who updated the motw.
     *
     * @ORM\ManyToOne(targetEntity="\InterpretersOffice\Entity\User")
     * @ORM\JoinColumn(nullable=false,name="created_by_id")
     */
    private $createdBy;


    /**
     * timestamp of last update.
     *
     * @ORM\Column(type="datetime",nullable=true)
     *
     * @var \DateTime
     */
    private $modified;


    /**
     * last User who updated the motw.
     *
     * @ORM\ManyToOne(targetEntity="\InterpretersOffice\Entity\User")
     * @ORM\JoinColumn(nullable=true,name="modified_by_id")
     */
    private $modifiedBy;

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
      * Set date.
      *
      * @param \DateTime $date
      *
      * @return MOTD
      */
     public function setWeekOf(DateTime $date) : NoteInterface
     {
         $this->week_of = $date;

         return $this;
     }

     /**
      * Get date.
      *
      * @return \DateTime
      */
     public function getWeekOf() : DateTime
     {
         return $this->week_of;
     }

     /**
      * implements JsonSerializable
      *
      * @return Array
      */
     public function jsonSerialize()
     {
         $data = ['content' => $this->getContent()];
         $data['id'] = $this->id;
         $data['week_of'] = $this->getDate()->format('D d-M-Y');
         $data['created_by'] = $this->getCreatedBy()->getUserName();
         $data['created'] = $this->getCreated()->format('D d-M-Y g:i a');
         $data['modified_by'] = $this->getModifiedBy() ?
            $this->getModifiedBy()->getUserName() : null;
         $data['modified'] = $this->getModified() ?
            $this->getModified()->format('D d-M-Y g:i a') : null;

         return $data;
     }

     /**
      * implements NoteInterface
      *
      * @return DateTime
      */
     public function getDate() : DateTime
     {
         return $this->getWeekOf();
     }

     /**
      * sets the "week_of"
      * @param  DateTime      $date
      * @return NoteInterface
      */
     public function setDate(DateTime $date) : NoteInterface
     {
         return $this->setWeekOf($date);
     }

     /**
      * Set content.
      *
      * @param string $content
      *
      * @return NoteInterface
      */
     public function setContent(string $content) : NoteInterface
     {
         $this->content = $content;

         return $this;
     }

     /**
      * Get content.
      *
      * @return string
      */
     public function getContent()  : string
     {
         return $this->content;
     }

     /**
      * Set created.
      *
      * @param \DateTime $created
      *
      * @return MOTD
      */
     public function setCreated(\DateTime $created)
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
      * @return MOTD
      */
     public function setModified(\DateTime $modified = null) : MOTD
     {
         $this->modified = $modified;

         return $this;
     }

     /**
      * Get modified.
      *
      * @return \DateTime|null
      */
     public function getModified() : ?DateTime
     {
         return $this->modified;
     }

     /**
      * Set createdBy.
      *
      * @param \InterpretersOffice\Entity\User $createdBy
      *
      * @return MOTD
      */
     public function setCreatedBy(\InterpretersOffice\Entity\User $createdBy)
     {
         $this->createdBy = $createdBy;

         return $this;
     }

     /**
      * Get createdBy.
      *
      * @return \InterpretersOffice\Entity\User
      */
     public function getCreatedBy() : User
     {
         return $this->createdBy;
     }

     /**
      * Set modifiedBy.
      *
      * @param User|null $modifiedBy
      *
      * @return MOTD
      */
     public function setModifiedBy(User $modifiedBy = null) :? User
     {
         $this->modifiedBy = $modifiedBy;

         return $this;
     }

     /**
      * Get modifiedBy.
      *
      * @return User|null
      */
     public function getModifiedBy() : ?User
     {
         return $this->modifiedBy;
     }

     /**
      * Lifecycle callback/sanity check for week_of.
      *
      * @ORM\PrePersist
      * @ORM\PreUpdate
      *
      * @throws \RuntimeException
      */
     public function onSave()
     {
         if ((int)$this->week_of->format('N') != 1) {
             new \RuntimeException(
                 sprintf('the "week_of" must be a Monday, but %s is a %s',
                 $this->week_of->format('d-M-Y'),
                 $this->week_of->format('l'))
             );
         }
     }
}
