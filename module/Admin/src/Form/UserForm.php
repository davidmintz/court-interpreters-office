<?php
/**
 * module/Admin/src/Form/JudgeForm.php.
 */

namespace InterpretersOffice\Admin\Form;

use Zend\Form\Form;
use InterpretersOffice\Form\CsrfElementCreationTrait;
use Doctrine\Common\Persistence\ObjectManager;

use InterpretersOffice\Admin\Form\UserFieldset;
use Zend\Validator\EmailAddress;
use Zend\Validator\NotEmpty;

/**
 * UserForm intended for administrative use
 *
 * @author david
 */
class UserForm extends Form
{

    use CsrfElementCreationTrait;

    /**
     * name of the form.
     *
     * @var string
     */
    protected $form_name = 'user-form';

    /**
     * constructor
     *
     * @param ObjectManager $objectManager
     * @param Array $options
     */

    public function __construct(ObjectManager $objectManager, $options = [])
    {

        parent::__construct($this->form_name, $options);
        $fieldset = new UserFieldset($objectManager, $options);
        if ($options['user']) {
            $user = $options['user'];
            $hat = $user->getPerson()->getHat();
            if ($hat->isJudgesStaff()) {
                $fieldset->addJudgeElement();
            }
        }
        $this->add($fieldset);
        $this->addCsrfElement();

        // make the email required
        $email_input = $this->getInputFilter()->get('user')
                ->get('person')->get('email');
        $email_input->setRequired(true)->setAllowEmpty(false)
                ->getValidatorChain()->attach(new NotEmpty());
    }
}
