<?php /** module/InterpretersOffice/src/Entity/LanguageCredential.php */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Models a credential -- certification, rating, classification -- for a
 * given InterpreterLanguage.
 *
 * An Interpreter might have a multiplicity of certifications or other
 * credentials in her/his working languages, but for our purposes the only one
 * that matters is whichever one has relevance to your Court's policies
 * and/or business rules. If, for example, you had a ratings system consisting
 * of "A", "B" and "C", and the fees paid to contract interpreters were tied to
 * those ratings, then you would need to have LanguageCredential entities called
 * "A", "B" and "C", and store those as attributes of the InterpreterLanguage
 * entity.
 *
 * @ORM\Entity
 * @ORM\Table(name="language_credentials")
 * @ORM\Entity(repositoryClass="InterpretersOffice\Entity\Repository\LanguageRepository")
 */
class LanguageCredential
{
    /**
     * entity id.
     *
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    private $id;

    /**
     * the full formal name of the credential or rating
     *
     * @ORM\Column(type="string",length=50,nullable=false)
     *
     * @var string
     */
    private $name;

    /**
     * abbreviation, for convenience and dropdown menus
     *
     * @ORM\Column(type="string",length=15,nullable=false)
     *
     * @var string
     */
    private $abbreviation;

    /**
     * verbose description or explanation
     *
     * @ORM\Column(type="string",length=400,nullable=false)
     *
     * @var string
     */
    private $description = '';

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
     * @return LanguageCredential
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
     * Set abbreviation.
     *
     * @param string $abbreviation
     *
     * @return LanguageCredential
     */
    public function setAbbreviation($abbreviation)
    {
        $this->abbreviation = $abbreviation;

        return $this;
    }

    /**
     * Get abbreviation.
     *
     * @return string
     */
    public function getAbbreviation()
    {
        return $this->abbreviation;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return LanguageCredential
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * stringify
     *
     * @return string
     */
    public function __toString()
    {
        return $this->abbreviation ?: $this->name;
    }
}
/*
CREATE TABLE `language_credentials` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `abbreviation` varchar(15) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `description` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name` (`name`),
  UNIQUE KEY `unique_abbrev` (`abbreviation`)
)
 */
