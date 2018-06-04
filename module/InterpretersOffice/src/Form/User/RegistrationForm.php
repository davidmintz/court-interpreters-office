<?php /** module/InterpretersOffice/src/Form/User/RegistrationForm.php */

namespace InterpretersOffice\Form\User;

use Zend\Form\Form;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Form\CsrfElementCreationTrait;
use InterpretersOffice\Admin\Form\UserFieldset;
use InterpretersOffice\Entity;
/**
 * user registration form
 */
class RegistrationForm extends Form
{

    use CsrfElementCreationTrait;

    /**
     * name of the form.
     *
     * @var string
     */
    protected $form_name = 'registration-form';

    /**
     * Doctrine entity manager
     *
     * @var ObjectManager
     */
    protected $objectManager;


    /**
     * constructor
     *
     * @param ObjectManager $objectManager
     */
     public function __construct($objectManager)
     {
         parent::__construct($this->form_name);
         $this->objectManager = $objectManager;
         $this->addCsrfElement();
         $fieldset = new UserFieldset($objectManager,
            ['action'=>'create', 'auth_user_role'=>'anonymous',]);
         $fieldset->addPasswordElements();
        // $fieldset->addPasswordValidators($this->getInputFilter());
         $this->add($fieldset);

     }
}
