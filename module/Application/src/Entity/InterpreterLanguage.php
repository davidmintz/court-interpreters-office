<?php

namespace Application\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entity representing an Interpreter's Language.
 * 
 * Technically, it is a language *pair*, but in this system it is understood that
 * the other language of the pair is English. There is a many-to-many relationship
 * between interpreters and languages. But because there is also metadata to record
 * about the language (federal certification), it is implemented as a Many-To-One
 * relationship on either side.
 * 
 * @ORM\Entity  
 * @ORM\Table(name="interpreters_languages") 
 *
 */
class InterpreterLanguage {
    
    
    public function __construct(Interpreter $interpreter = null, 
            Language $language = null)
    {

        if ($interpreter) {
            $this->setInterpreter($interpreter);
        }
        if ($language) {
            $this->setLanguage($language);
        }
    }

    /**
     * @ORM\ManyToOne(targetEntity="Interpreter",inversedBy="interpreterLanguages")
     * @ORM\Id
     * @var Interpreter
     */
    protected $interpreter;
    
    /**
     * @ORM\ManyToOne(targetEntity="Language",inversedBy="interpreterLanguages")
     * @ORM\Id
     * @var Language 
     */
    protected $language;
    
    /**
     * @ORM\Column(name="federal_certification",type="boolean",nullable=true)
     * @var boolean
     */
    protected $federalCertification;

    /**
     * Set interpreter
     *
     * @param \Application\Entity\Interpreter $interpreter
     *
     * @return InterpreterLanguage
     */
    public function setInterpreter(\Application\Entity\Interpreter $interpreter)
    {
        $this->interpreter = $interpreter;

        return $this;
    }

    /**
     * Get interpreter
     *
     * @return \Application\Entity\Interpreter
     */
    public function getInterpreter()
    {
        return $this->interpreter;
    }

    /**
     * Set language
     *
     * @param \Application\Entity\Language $language
     *
     * @return InterpreterLanguage
     */
    public function setLanguage(\Application\Entity\Language $language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return \Application\Entity\Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set federalCertification
     *
     * @param boolean $federalCertification
     *
     * @return InterpreterLanguage
     */
    public function setFederalCertification($federalCertification)
    {
        $this->federalCertification = $federalCertification;

        return $this;
    }

    /**
     * Get federalCertification
     *
     * @return boolean
     */
    public function getFederalCertification()
    {
        return $this->federalCertification;
    }
}
