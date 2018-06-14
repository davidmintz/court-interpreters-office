<?php /** module/InterpretersOffice/src/Form/User/RegistrationForm.php */

namespace InterpretersOffice\Form\User;

use Zend\Form\Form;
use Zend\Validator\ValidatorChain;
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

    /*\
     * Doctrine entity manager
     protected $objectManager;
     *
     * @var ObjectManager
     */


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

    }
}
