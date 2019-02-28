<?php

/** module/InterpretersOffice/src/Entity/InterpreterLanguage.php   */

namespace InterpretersOffice\Entity;

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
 */
class InterpreterLanguage
{
    /**
     * constructor.
     *
     * @param Interpreter $interpreter
     * @param Language    $language
     *
     * @todo a lifecycle callback to ensure certified languages have a boolean
     * $federalCertification set
     */
    public function __construct(
        Interpreter $interpreter = null,
        Language $language = null
    ) {
        $this->interpreter = $interpreter;
        $this->language = $language;
    }

    /**
     * The Interpreter who works in this language.
     *
     * @ORM\ManyToOne(targetEntity="Interpreter",inversedBy="interpreterLanguages")
     * @ORM\Id
     *
     * @var Interpreter
     */
    protected $interpreter;

    /**
     * The language in which this interpreter works.
     *
     * @ORM\ManyToOne(targetEntity="Language",inversedBy="interpreterLanguages",fetch="EAGER")
     * @ORM\Id
     *
     * @var Language
     */
    protected $language;

    /**
     * Whether the Interpreter holds federal court interpreter certification in this language.
     *
     * The only certified languages in the US District Court system are Spanish,
     * Navajo and Haitian Creole. Of these, only the Spanish certification
     * program is active. This field should be a boolean for the certified
     * languages and null for everything else.
     *
     * @link http://www.uscourts.gov/services-forms/federal-court-interpreters/federal-court-interpreter-certification-examination the federal court certification program
     *
     * @ORM\Column(name="federal_certification",type="boolean",nullable=true)
     *
     * @var bool
     */
    protected $federalCertification;

    /**
     * Set interpreter.
     *
     * @param \InterpretersOffice\Entity\Interpreter $interpreter
     *
     * @return InterpreterLanguage
     */
    public function setInterpreter(Interpreter $interpreter = null)
    {
        $this->interpreter = $interpreter;

        return $this;
    }

    /**
     * Get interpreter.
     *
     * @return Interpreter
     */
    public function getInterpreter()
    {
        return $this->interpreter;
    }

    /**
     * Set language.
     *
     * @param Language $language
     *
     * @return InterpreterLanguage
     */
    public function setLanguage(Language $language = null)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language.
     *
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set federalCertification.
     *
     * @param bool $federalCertification
     *
     * @return InterpreterLanguage
     */
    public function setFederalCertification($federalCertification)
    {
        if ((string)$federalCertification == "-1") {
            $federalCertification = null;
        }
        $this->federalCertification = $federalCertification;

        return $this;
    }

    /**
     * Get federalCertification.
     *
     * @return bool
     */
    public function getFederalCertification()
    {
        return $this->federalCertification;
    }

    /**
     * return this entity as an array [ language_id => federalCertification ].
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'language_id' => $this->language->getId(),
            'federalCertification' => $this->getFederalCertification(),
        ];
    }

    /**
     * is the language among the federal certification languages?
     * @throws \RuntimeException
     * @return boolean
     */
    public function isCertifiable()
    {
        $language = $this->getLanguage();
        if (! $language) {
            throw new \RuntimeException('language entity must be set before calling '.__FUNCTION__);
        }
        return $language->isFederallyCertified();
    }
}
