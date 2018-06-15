<?php /** module/InterpretersOffice/src/Form/User/RegistrationForm.php */

namespace InterpretersOffice\Form\User;

use Zend\Form\Form;
use Zend\Validator\ValidatorChain;
use Zend\Validator\Callback;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Form\CsrfElementCreationTrait;
use InterpretersOffice\Form\ObjectManagerAwareTrait;
use InterpretersOffice\Form\User\RegistrationForm;
use InterpretersOffice\Admin\Form\UserFieldset;
use InterpretersOffice\Entity;


/**
 * user registration form
 */
class RegistrationForm extends Form
{

    use CsrfElementCreationTrait;
    use ObjectManagerAwareTrait;

    /**
     * name of the form.
     *
     * @var string
     */
    protected $form_name = 'registration-form';


    /**
     * (possible) existing user -- experimental
     *
     * @var Entity\User
     */
     protected $existing_user;

     /**
      * sets the existing user found in the database
      *
      * this is experimental.
      *
      * @param Entity\User $user
      */
     public function setExistingUser(Entity\User $user)
     {
         $this->existing_user = $user;
     }
    /**
     * examines input and conditionally modifies validators
     *
     * @param Array $input
     * @return RegistrationForm
     */
     public function preValidate(Array $input)
     {
         $hat_id = $input['person']['hat'];
         $inputFilter = $this->getInputFilter();
         $inputFilter->get('user')->get('judges')
             ->setRequired(false)
             ->setAllowEmpty(true);
         if (! $hat_id) {
             // we can't know if judges are required or not
            return $this;
         }
         $hat = $this->objectManager->find(Entity\Hat::class,$hat_id);
         // if it's a judge-staff kind of Hat, judges element is required
         if ($hat->getIsJudgeStaff()) {
             $inputFilter->get('user')->get('judges')
                ->setAllowEmpty(false)
                ->setRequired(true)
                ->getValidatorChain()->attachByName('NotEmpty',
                 ['messages'=>['isEmpty'=>'a Judge is required']],
                 true);
         }
     }

    /**
     * constructor
     *
     * @param ObjectManager $objectManager
     * @param Array $options
     */

    public function __construct(ObjectManager $objectManager, $options = [])
    {

        parent::__construct($this->form_name, $options);
        $this->objectManager = $objectManager;
        $user_fieldset = new UserFieldset($objectManager, $options);
        $user_fieldset->addPasswordElements()->addJudgeElement();
        $this->add($user_fieldset);
        $this->addCsrfElement();

        // we will set these ourself
        $inputFilter = $this->getInputFilter();
        $inputFilter->get('user')->get('role')
            ->setRequired(false)
            ->setAllowEmpty(true);
        $inputFilter->get('user')->get('person')->get('active')
            ->setRequired(false)
            ->setAllowEmpty(true);

        $inputFilter->get('user')->get('username')
            ->setRequired(false)->setAllowEmpty(true);

        // add password validation
        $inputFilter->get('user')->get('password')
            ->setRequired(true)
            ->setAllowEmpty(false)
            ->getValidatorChain()->attachByName('NotEmpty',
                ['messages'=>['isEmpty'=>'password is required']],
                true
            )->attachByName('StringLength',
            [
                'min' => 8,'max'=>150, 'messages'=>
                [
                'stringLengthTooShort'=>
                    'password is too short (minimum %min% characters)',
                'stringLengthTooLong'=>
                    'password exceeds maximum length (%max% characters)',
                ]
        ]);
        // make the email required
        $email_input = $this->getInputFilter()->get('user')
                ->get('person')->get('email');
        $email_input->setAllowEmpty(false)->setRequired(true)
                ->getValidatorChain()->prependByName(
                    'NotEmpty',
                    ['messages'=>['isEmpty'=>'email is required']],
                    true
                );

        // password
        $inputFilter->get('user')->get('password-confirm')->getValidatorChain()
            ->attachByName('NotEmpty',
            ['messages'=>['isEmpty'=>'password confirmation is required']],
                true
            )
            ->attachByName('Identical',[
                'token' =>'password', // this actually works. don't fuck it up.
                'messages'=> [
                    'notSame'=>'password and password confirmation do not match'
                ],
            ]);

        // filter: trim
        foreach (['password','password-confirm'] as $field) {
            $inputFilter->get('user')->get($field)->getFilterChain()
                ->attachByName('StringTrim');
        }

        // tweak error message for "hat" element
        $chain = $inputFilter->get('user')->get('person')->get('hat')
            ->getValidatorChain();
        /** @var \Zend\Validator\NotEmpty $shit */
        $shit = $chain->getValidators()[0]['instance'];
        $shit->setOptions(['messages'=>
            [ 'isEmpty' => 'job title or department is required' ]
        ]);

        // make sure there is not already an existing user account
        /** @var Zend\Validator\ValidatorChain $chain */
        $chain = $inputFilter->get('user')->get('person')->get('email')->getValidatorChain();
        $objectManager = $this->objectManager;
        $form = $this;
        $validator = new Callback([
            'callback' => function($value, $context) use ($objectManager,$form){
                $repo = $objectManager->getRepository(Entity\User::class);
                $user = $repo->findSubmitterByEmail($value);
                if ($user) {
                    // maybe: this is experimental, with a view to getting
                    // more information about the status of the duplicate
                    // account, i.e., has it ever been used? is it active?
                    // what Hat is it associated with? maybe a shitty
                    // design approach but this is the idea.
                    $form->setExistingUser($user);
                    // definitely need to...
                    return false;
                }
                return true;
            },
                'messages' => [
                    Callback::INVALID_VALUE =>
                    'There is already a user account associated with this email address.'
                ]
            ]
        );
        $chain->prependValidator($validator,true);
    }
    /**
     * returns flattened error messages
     *
     * @return Array
     */
    function getFlattenedErrorMessages()
    {
        $errors = $this->getMessages();
        if (isset($errors['user'])) {
            if (isset($errors['user']['person'])) {
                $errors = array_merge($errors,$errors['user']['person']);
                unset($errors['user']['person']);
            }
            $errors = array_merge($errors, $errors['user']);
            unset($errors['user']);
        }
        return $errors;
    }
}
