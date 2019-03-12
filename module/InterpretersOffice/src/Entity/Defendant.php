<?php

/** module/InterpretersOffice/src/Entity/Defendant.php */

namespace InterpretersOffice\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

/**
 * Entity modeling a defendant for whom an interpreter is required.
 *
 * In reality, the Defendant entity models a name as opposed
 * to a person. This is because we expect names to recur in the context of more than
 * one docket number, and we recycle them. We usually don't know or care about the
 * actual identity of the defendant, so don't attempt to associate directly a name
 * with a docket number.
 *
 * @ORM\Entity  @ORM\Table(name="defendant_names",uniqueConstraints={@ORM\UniqueConstraint(name="unique_deftname",columns={"given_names", "surnames"})})
 * @ORM\Entity(repositoryClass="InterpretersOffice\Entity\Repository\DefendantRepository")
 */

class Defendant implements \ArrayAccess
{
    /**
     * entity id.
     *
     * @ORM\Id @ORM\GeneratedValue(strategy="AUTO")  @ORM\Column(type="integer",options={"unsigned":true})
     */
    protected $id;

    /**
     * given name(s), a/k/a first name(s).
     *
     * @ORM\Column(type="string",name="given_names",length=60,nullable=false)
     *
     * @var string
     */
    protected $given_names;

    /**
     * surname(s), a/k/a last name.
     *
     * @ORM\Column(type="string",length=60,nullable=false)
     *
     * @var string
     */
    protected $surnames;

    /**
     * is this name is spelled exactly like $name?
     *
     * @param  Defendant $name
     * @return boolean true if $name is the same in all respects except id
     */
    public function equals(Defendant $name)
    {
        return $this->given_names == $name->getGivenNames()
        && $this->surnames == $name->getSurnames()
        && $name->getId() != $this->getId();
    }

    /**
     * returns "lastname, firstname".
     *
     * @return string
     */
    public function getFullName()
    {
        $fullName = $this->surnames;
        if ($this->given_names) {
            $fullName .= ", $this->given_names";
        }

        return $fullName;
    }

    /**
     * returns string representation of the entity.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getLastname().', '.$this->getFirstname();
    }

    /**
     * convenience method to set full name in one shot.
     *
     * @param string $surnames
     * @param string $given_names
     *
     * @return Defendant
     */
    public function setFullname($surnames, $given_names)
    {
        $this->surnames = $surnames;
        $this->given_names = $given_names;

        return $this;
    }
    /**
     * Get deftId.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set firstname
     * proxies to setGivenNames.
     *
     * @param string $firstname
     *
     * @return Defendant
     */
    public function setFirstname($firstname)
    {
        return $this->setGivenNames($firstname);
    }

    /**
     * Get firstname
     * proxies to getGivenNames.
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->getGivenNames();
    }

    /**
     * Set lastname.
     *
     * proxies to setSurnames().
     *
     * @param string $lastname
     *
     * @return defendant
     */
    public function setLastname($lastname)
    {
        return $this->setSurnames($lastname);
    }

    /**
     * Get lastname
     * prxiues to getSurnames.
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->getSurnames();
    }

    /**
     * Get surnames.
     *
     * @return string
     */
    public function getSurnames()
    {
        return $this->surnames;
    }

    /**
     * set surnames.
     *
     * @param string $surnames
     *
     * @return defendant
     */
    public function setSurnames($surnames)
    {
        $this->surnames = $surnames;

        return $this;
    }

    /**
     * Get given names.
     *
     * @return string
     */
    public function getGivenNames()
    {
        return $this->given_names;
    }
    /**
     * set given names.
     *
     * @param string $given_names
     *
     * @return defendant
     */
    public function setGivenNames($given_names)
    {
        $this->given_names = $given_names;

        return $this;
    }

    /* Methods
    abstract public offsetExists ( mixed $offset ) : bool
    abstract public offsetGet ( mixed $offset ) : mixed
    abstract public offsetSet ( mixed $offset , mixed $value ) : void
    abstract public offsetUnset ( mixed $offset ) : void
    }
    */

   /**
    * implements \ArrayAccess
    * @param string $offset
    * @return boolean
    */
   public function offsetExists($offset) {
       return in_array($offset,['given_names','surnames','id']);
   }

   /**
    *implements \ArrayAccess
    * @param string $offset
    * @return void
    */
   public function offsetUnset($offset) {
       // noop
   }
   /**
    * implements \ArrayAccess
    * @param string $offset
    * @return string
    */
   public function offsetGet($offset) {

       if ($offset == 'given_names') {
           return $this->getGivenNames();
       } elseif ($offset == 'surnames') {
           return $this->getSurnames();
       }  elseif ($offset == 'id') {
           return $this->getId();
       } else {
           // too bad
       }
   }

   /**
    * implements \ArrayAccess
    * @param string $offset
    * @param string $value
    * @return void
    */
   public function offsetSet($offset,$value) {
       if ($offset == 'given_names') {
           $this->setGivenNames($value);
       } elseif ($offset == 'surnames') {
           $this->setSurnames($value);
       } else {
           // too bad
       }
   }
}
