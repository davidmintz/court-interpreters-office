<?php

/** module/InterpretersOffice/src/Entity/EventEmail.php */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;
//use Doctrine\Common\Collections\Collection;
//use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity representing a row/entry in the event_emails table/log
 *
 * @ORM\Entity
 * @ORM\Table(name="event_emails")
 *
 */
class EventEMail {

    /**
     * entity id.
     *
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     * @var int
     */
    private $id;

    /**
     * related Event
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(nullable=true)
     * @var Event
     */
    private $event;

    /**
     * timestamp
     *
     * @ORM\Column(type="datetime",nullable=false)
     * @var \DateTime
     */
    private $timestamp;

    /**
     * Person to whom mail was sent
     *
     * @ORM\ManyToOne(targetEntity="Person")
     * @ORM\JoinColumn(nullable=true)
     *
     * @var Person
     */
    private $recipient;

    /**
     * User who sent the email
     * 
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=true,name="user_id")
     *
     * @var User
     */
    private $sender;

    /**
     * recipient email address.
     *
     * @ORM\Column(type="string",length=60,nullable=false)
     * @var string
     */
    private $email;

    /**
     * recipient email address.
     *
     * @ORM\Column(type="string",length=250,nullable=false)
     * @var string
     */
    private $subject;

    /**
     * comments. e.g., cc
     *
     * @ORM\Column(type="string",length=250,nullable=false)
     *
     * @var string
     */
    private $comments;

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
     * Set timestamp.
     *
     * @param \DateTime $timestamp
     *
     * @return EventEMail
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Get timestamp.
     *
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return EventEMail
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set subject.
     *
     * @param string $subject
     *
     * @return EventEMail
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject.
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set comments.
     *
     * @param string $comments
     *
     * @return EventEMail
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments.
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set event.
     *
     * @param \InterpretersOffice\Entity\Event|null $event
     *
     * @return EventEMail
     */
    public function setEvent(\InterpretersOffice\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event.
     *
     * @return \InterpretersOffice\Entity\Event|null
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set recipient.
     *
     * @param \InterpretersOffice\Entity\Person|null $recipient
     *
     * @return EventEMail
     */
    public function setRecipient(\InterpretersOffice\Entity\Person $recipient = null)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * Get recipient.
     *
     * @return \InterpretersOffice\Entity\Person|null
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * Set sender.
     *
     * @param \InterpretersOffice\Entity\User|null $sender
     *
     * @return EventEMail
     */
    public function setSender(\InterpretersOffice\Entity\User $sender = null)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender.
     *
     * @return \InterpretersOffice\Entity\User|null
     */
    public function getSender()
    {
        return $this->sender;
    }
}
