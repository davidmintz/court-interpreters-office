<?php /** module/InterpretersOffice/src/Form/User/RegistrationForm.php */

namespace InterpretersOffice\Form\User;

use Zend\Form\Form;
use Zend\Validator\ValidatorChain;
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
     * examines input and conditionally modifies validators
     *
     * @param EventInterface $e
     * @return void
     */
     public function preValidate(Array $input)
     {
         return print_r($input,true);
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

        $inputFilter->get('user')->get('password-confirm')->getValidatorChain()
            ->attachByName('NotEmpty',
            ['messages'=>['isEmpty'=>'password confirmation is required']],
            true
            )
            ->attachByName('Identical',[
                'token' => ['user' => 'password'],
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
        $shit->setOptions( ['messages'=> ['isEmpty'=>'job title or department is required']]);



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
