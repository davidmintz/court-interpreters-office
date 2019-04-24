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
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=true)
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

}
