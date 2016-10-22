<?php

namespace Application\Entity;
use Doctrine\ORM\Mapping as ORM;

/** 
* Entity modeling a defendant for whom an interpreter is required.
* 
* In reality, the DefendantName entity models just that: a name, as opposed 
* to a person. This is because we expect names to recur in the context of more than
* one docket number, and we recycle them. We usually don't know or care about the 
* actual identity of the defendant, so don't attempt to associate directly a name 
* with a docket number.
* 
* @ORM\Entity  @ORM\Table(name="defendant_names",uniqueConstraints={@ORM\UniqueConstraint(name="unique_deftname",columns={"given_names", "surnames"})}) 
* //ORM\Entity(repositoryClass="Application\Entity\DefendantNameRepository") 
*/
class DefendantName 
{


    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     */
    protected $id;

 	/**
     * @ORM\Column(type="string",name="given_names",length=60,nullable=false)
     * @var string
     */
    protected $givenNames;

    /**
     * @ORM\Column(type="string",length=60,nullable=false)
     * @var string
     */
    protected $surnames;

    /**
     * returns "lastname, firstname"
     * @return string
     */
    public function getFullName() {
        $fullName = $this->surnames;
        if ($this->given_names) {
            $fullName .= ", $this->given_names";
        }
        return $fullName;
    }

    /**
    * returns string representation of the entity
    * @return string
    */
    public function __toString() {
       return $this->getLastname(). ', ' . $this->getFirstname();
    }

    /**
    * convenience method to set full name in one shot
    * @param string $surnames
    * @params string $given_names
    * @return DefendantName
    */
    public function setFullname($surnames, $givenNames)
    {
        $this->surnames = $surnames;
        $this->givenNames = $givenName;
        return $this;
    }
    /**
     * Get deftId
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set firstname
     * proxies to setGivenNames
     * @param string $firstname
     * @return DefendantName
     */
    public function setFirstname($firstname)
    {
       
        return $this->setGivenNames($firstname);
    }

    /**
     * Get firstname
     * proxies to getGivenNames
     * @return string
     */
    public function getFirstname()
    {
        return $this->getGivenNames();
    }

    /**
     * Set lastname
     * proxies to setSurnames()
     * @param string $lastname
     *
     * @return DefendantName
     */
    public function setLastname($lastname)
    {
        
        return $this->setSurnames($lastname);
    }

    /**
     * Get lastname
     * prxiues to getSurnames
     * @return string
     */
    public function getLastname()
    {
        return $this->getSurnames();
    }
    
    /**
     * Get surnames
     *
     * @return string
     */
    public function getSurnames() {
        return $this->surnames;
    }
    
    /**
     * set given names
     *
     * @return DefendantName
     */
    public function setSurnames($surnames) {
        
        $this->surames = $surnames;
        return $this;
    }
    
    /**
     * Get given names
     * @return string
     */
    public function getGivenNames()  {
        
        return $this->givenNames;
    }
    /**
     * set given names
     *
     * @return DefendantName
     */
    public function setGivenNames($given_names) {
        
        $this->givenNames = $given_names;
        return $this;
    }
}
