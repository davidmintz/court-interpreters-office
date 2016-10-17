<?php

namespace Application\Entity;
use Doctrine\ORM\Mapping as ORM;

/** 
*  @ORM\Entity  @ORM\Table(name="defendant_names",uniqueConstraints={
    @uniqueConstraint(name="unique_deftname",columns="{given_names, surnamess}")}) 
*  //ORM\Entity(repositoryClass="Application\Entity\DefendantNameRepository") 
*/
class DefendantName 
{


    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     */
    protected $id;

 	/**
     * @ORM\Column(type="string",name="given_names")
     * @var string
     */
    protected $givenNames;

    /**
     * @ORM\Column(type="string")
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

    public function __toString() {
       return $this->getLastname(). ', ' . $this->getFirstname();
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
