<?php
/**
 * module/Admin/src/Form/UserForm.php
 */

namespace InterpretersOffice\Admin\Form;

use Zend\Form\Form;
use InterpretersOffice\Form\PersonFieldset;
use InterpretersOffice\Form\CsrfElementCreationTrait;
use Doctrine\Common\Persistence\ObjectManager;

use InterpretersOffice\Admin\Form\UserFieldset;
use Zend\Validator\EmailAddress;
use Zend\Validator\ValidatorChain;
use Zend\Validator\Callback;
use Zend\Validator\NotEmpty;
use Zend\InputFilter\Input;

use InterpretersOffice\Entity;

use \password_verify;

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
        if (key_exists('user', $options)) {
            $user = $options['user'];
            $hat = $user->getPerson()->getHat();
            if ($hat->isJudgesStaff()) {
                $fieldset->addJudgeElement();
            }
            $fieldset->setObject($user);
        }
        $this->add($fieldset);
        $this->addCsrfElement();

        // make the email required
        $email_input = $this->getInputFilter()->get('user')
                ->get('person')->get('email');
        $email_input->setRequired(true)->setAllowEmpty(false)
            ->getValidatorChain()->attach(new NotEmpty(
                ['messages' => ['isEmpty' => 'email is required']]
            ));
    }

    /**
     * adds password validation
     *
     * 
     */
    public function addPasswordValidators()
    {
        $inputFilter = $this->getInputFilter();
        $input = new Input('password');
        $chain = $input->getValidatorChain();
        $input->getFilterChain()->attachByName('StringTrim');
        $chain->attachByName('NotEmpty', [
                'required' => true,
                'break_chain_on_failure'=> true,
                'messages' => ['isEmpty' => 'password field is required',]
                , true])
            ->attachByName('StringLength', ['min' => 8,'max' => '150','messages' => [
                'stringLengthTooLong' => 'password length exceeds maximum (150 characters)',
                'stringLengthTooShort' => 'password length must be a minimum of 8 characters',
            ]], true);
        $inputFilter->get('user')->add($input);
        $confirmation_input = new Input('password-confirm');
        $confirmation_input->getFilterChain()->attachByName('StringTrim');
        $chain = $confirmation_input->getValidatorChain()
            ->attachByName('NotEmpty', [
                'required' => true,
                'break_chain_on_failure'=> true,
                'messages' => ['isEmpty' => 'password-confirmation field is required',]
                , true])
  
            ->attachByName('Identical', ['token' => 'password','messages' => [
                'notSame' => 'password confirmation field does not match'
        ]]);
        $inputFilter->get('user')->add($confirmation_input);
    }

    /**
     * adds (current) password element and validators for user/profile
     *
     * @return UserForm
     */
    public function addCurrentPasswordElement()
    {
        $fs = $this->get('user');
        $fs->add([
            'type' => 'password','name' => 'current-password',
            'attributes' => ['class' => 'form-control','id' => 'current-password']
        ]);
        $inputFilter = $this->getInputFilter();
        $input = new Input('current-password');
        $input->getFilterChain()->attachByName('StringTrim');
        $chain = $input->getValidatorChain();
        $hash = $fs->getObject()->getPassword();
        $chain->attachByName('NotEmpty', [
            'required' => true,
            'allow_empty' => false,
            'break_chain_on_failure'=> true,
            'messages' => ['isEmpty' => 'current password is required',]
            ])
            ->attachByName('Callback',[
                'callback'=>function($password) use ($hash) {
                    /** @todo trigger a failed-authentication/security event? */
                    return password_verify($password,$hash);},
                'messages' => [
                    Callback::INVALID_VALUE => 'Authentication failed: invalid password.'
                ]
            ]);
        $inputFilter->get('user')->add($input);

        return $this;
    }

    public function addUniqueEmailValidator()
    {
        $inputFilter = $this->getInputFilter();
        $chain = $inputFilter->get('user')->get('person')->get('email')->getValidatorChain();
        $repo = $this->get('user')->getObjectManager()->getRepository(Entity\User::class);
        $user = $this->get('user')->getObject();
        $chain->attachByName('Callback',[
            'callback'=>function($email) use ($repo, $user) {
                $count = $repo->countExistingUserEmail($user,$email);
                return ! $count;
            },
            'messages' => [
                Callback::INVALID_VALUE => 'This email address is currently in use by another user.'
            ]
        ]);
    }
}
