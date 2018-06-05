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
     * @param Array $options
     */

    public function __construct(ObjectManager $objectManager, $options = [])
    {

        parent::__construct($this->form_name, $options);
        $user_fieldset = new UserFieldset($objectManager, $options);
        $user_fieldset->addPasswordElements();
        $this->add($user_fieldset);
        $this->addCsrfElement();

        $this->getInputFilter()->get('user')->get('role')
            ->setRequired(false)
            ->setAllowEmpty(true);
        $this->getInputFilter()->get('user')->get('person')->get('active')
            ->setRequired(false)
            ->setAllowEmpty(true);
    }

    /**
     * (not) constructor
     *
     * @param ObjectManager $objectManager
     */
     public function __fuckedconstruct($objectManager)
     {
         $this->objectManager = $objectManager;
         parent::__construct($this->form_name);
         $this->addCsrfElement();
         $fieldset = new UserFieldset($objectManager,
            ['action'=>'create', 'auth_user_role'=>'anonymous',]);
         $fieldset->addPasswordElements();
        // $fieldset->addPasswordValidators($this->getInputFilter());
         $this->add($fieldset);

     }
}
