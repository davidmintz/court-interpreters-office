<?php
/** module/Application/src/Entity/Person.php */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
/**
 * Entity representing a person in the court interpreters office management 
 * system.
 * 
 * The Person's Hat property identifies the "hat" that the Person wears. People 
 * have been known to change Hats in the course  of the underlying databases' 
 * lifetime. For example, a Law Clerk becomes an AUSA who becomes a Judge; a 
 * contract interpreter becomes a staff interpreter. Our strategy for dealing 
 * with this is reincarnation: set the former hat-person's active property 
 * to <code>false</code> and the new hat-person's active  property to 
 * <code>true</code>. The database table's unique index constraint 
 * ensures that no two rows can have the same hat_id and email. But because we 
 * have to permit emails to be NULL in some instances, the uniqueness of the 
 * active Person has to be further enforced at the application level.
 * 
 * Note to self: try Annotation/Type("Fieldset") and see if it works. 
 * http://stackoverflow.com/questions/12002722/using-annotation-builder-in-extended-zend-form-class/18427685#18427685
 * 
 * @see Application\Entity\Hat
 * @see Application\Entity\Judge
 * @see Application\Entity\Interpreter
 * 
 * @Annotation\Name("person")
 * @Annotation\Type("Form")
 * 
 * @ORM\Entity  @ORM\Table(name="people",uniqueConstraints={@ORM\UniqueConstraint(name="hat_email_idx",columns={"email","hat_id"})})
 * @ORM\InheritanceType("JOINED")
 *
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"person" = "Person", "interpreter"="Interpreter", "judge"="Judge"})
 */



class Person
{
    /**
     * entity id
     * @Annotation\Exclude()
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    protected $id;

    /**
     * the Person's email address.
     * 
     * @ORM\Column(type="string",length=50,nullable=true)
     *
     * @var string
     */
    protected $email;

    /**
     * the Person's last name.
     * 
     * @Annotation\Options({"label":"last name"})
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Validator({"name":"NotEmpty",
     *  "break_chain_on_failure": true,
     *  "options":{"messages":{"isEmpty":"last name is required"}
     *  }}) 
     * @Annotation\Validator({
     *  "break_chain_on_failure": true,
     *  "name":"Application\Form\Validator\ProperName",
     *  "options" : {"type":"last"}
     * })
     * @Annotation\Validator({"name":"StringLength", "options":{"min":2, "max":50,
     *  "messages":{"stringLengthTooShort":"name must be at least 2 characters long",
     *   "stringLengthTooLong":"name exceeds maximum length of 50 characters"}}})
     * 
     * @ORM\Column(type="string",length=50,nullable=false)
     * @var string
     */
    protected $lastname;

    /**
     * the Person's first name.
     * 
     * @Annotation\Options({"label":"first name"})
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\Validator({"name":"NotEmpty",
     *  "break_chain_on_failure": true,
     *  "options":{"messages":{"isEmpty":"first name is required"}
     *  }}) 
     * @Annotation\Validator({
     *  "break_chain_on_failure": true,
     *  "name":"Application\Form\Validator\ProperName",
     *  "options" : {"type":"first"}
     * })
     * @Annotation\Validator({"name":"StringLength", "options":{"min":2, "max":50,
     *  "messages":{"stringLengthTooShort":"name must be at least 2 characters long",
     *   "stringLengthTooLong":"name exceeds maximum length of 50 characters"}}})
     *      
     * @ORM\Column(type="string",length=50,nullable=false)
     *
     * @var string first name, or given names
     */
    protected $firstname;
    
     /**
     * the Person's middle name or initial
     * @Annotation\Options({"label":"middle name"})
     * @Annotation\Filter({"name":"StringTrim"})
     * @Annotation\AllowEmpty()
     * @Annotation\Validator({
     *  "break_chain_on_failure": true,
     *  "name":"Application\Form\Validator\ProperName",
     *  "options" : {"type":"middle"}
     * })
     * @Annotation\Validator({"name":"StringLength", "options":{"min":2, "max":50,
     *  "messages":{"stringLengthTooShort":"name must be at least 2 characters long",
     *   "stringLengthTooLong":"name exceeds maximum length of 50 characters"}}})
     * @ORM\Column(type="string",length=50,nullable=false,options={"default":""})
     *
     * @var string middle name or initial
     */
    protected $middlename = '';

    /**
     *  Everyone must where a hat in this life.
     *  
     *  @ORM\ManyToOne(targetEntity="Hat",fetch="EAGER")
     *  @ORM\JoinColumn(nullable=false)
     *  
     *  @var Hat
     */
    protected $hat;
    
    /**
     * Is the person "active," or only of historical interest?
     * 
     * If false, the entity should not be displayed in dropdown menus.
     * 
     * @ORM\Column(type="boolean",nullable=false)
     * @var boolean
     */
    protected $active;

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
     * Set email.
     *
     * @param string $email
     *
     * @return Person
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
     * Set lastname.
     *
     * @param string $lastname
     *
     * @return Person
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname.
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set firstname.
     *
     * @param string $firstname
     *
     * @return Person
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname.
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }
    
    /**
     * Set middle name.
     *
     * @param string $middlename
     *
     * @return Person
     */
    public function setMiddlename($middlename)
    {
        $this->middlename = $middlename;

        return $this;
    }

    /**
     * Get middle name.
     *
     * @return string
     */
    public function getMiddlename()
    {
        return $this->middlename;
    }

    /**
     * Set hat.
     *
     * @param \Application\Entity\Hat $hat
     *
     * @return Person
     */
    public function setHat(Hat $hat)
    {
        $this->hat = $hat;

        return $this;
    }

    /**
     * Get hat.
     *
     * @return Hat
     */
    public function getHat()
    {
        return $this->hat;
    }
   
    /**
     * is the person "active?"
     * 
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }
    /**
     * sets the "active" property
     * 
     * @param boolean $active
     * @return Person
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }
}
