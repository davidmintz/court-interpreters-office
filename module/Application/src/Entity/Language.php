<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity  @ORM\Table(name="languages",uniqueConstraints={@ORM\UniqueConstraint(name="unique_language",columns={"name"})}) */
class Language
{
    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    protected $id;

    /**
     * @ORM\Column(type="string",length=50,nullable=false)
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="string",length=200,nullable=false,options={"default":""})
     *
     * @var string
     */
    protected $comments;

    /**
     * @ORM\OneToMany(targetEntity="InterpreterLanguage",mappedBy="language")
     *
     * @var InterpreterLanguage[]
     */
    protected $interpreterLanguages;

    /**
     * Is there a federal certification program for this language?
     *
     * There are only three such languages and that is very unlikely to change.
     * So unlikely that we can hard-code them.
     *
     * @return bool
     */
    public function is_federally_certified()
    {
        return in_array($this->name, [
            'Spanish',
            'Navajo',
            'Haitian Creole',
        ]);
    }

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
     * Set name.
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
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set comments.
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
     * Get comments.
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->interpreterLanguages = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add interpreterLanguage.
     *
     * @param \Application\Entity\InterpreterLanguage $interpreterLanguage
     *
     * @return Language
     */
    public function addInterpreterLanguage(\Application\Entity\InterpreterLanguage $interpreterLanguage)
    {
        $this->interpreterLanguages[] = $interpreterLanguage;

        return $this;
    }

    /**
     * Remove interpreterLanguage.
     *
     * @param \Application\Entity\InterpreterLanguage $interpreterLanguage
     */
    public function removeInterpreterLanguage(\Application\Entity\InterpreterLanguage $interpreterLanguage)
    {
        $this->interpreterLanguages->removeElement($interpreterLanguage);
    }

    /**
     * Get interpreterLanguages.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInterpreterLanguages()
    {
        return $this->interpreterLanguages;
    }
}
