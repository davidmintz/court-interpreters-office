<?php 
/** module/InterpretersOffice/src/Form/PersonForm.php */
namespace InterpretersOffice\Form;

use Zend\Form\Form as ZendForm;
use Zend\Form\Element\Csrf;
//use Zend\InputFilter\InputFilterProviderInterface;
//use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Zend\InputFilter;

/**
 * Form for a Person entity.
 */
class PersonForm extends ZendForm //implements ObjectManagerAwareInterface
{
	//use ObjectManagerAwareTrait;
    
    /** 
     * name of the form
     * 
     * @var string
     */
    protected $formName = 'person-form';
    
    /**
     * name of Fieldset class to instantiate and add to the form.
     * 
     * the idea is that subclasses can override this. 
     * 
     * @var string
     */
    protected $fieldsetClass = PersonFieldset::class;
    
    /**
     * constructor
     * 
     * @todo figure out how best to make it easy/obligatory for subclasses to 
     * provide their own validation to certain elements. extend an abstract class
     * with an abstract getAdditionalInputFilter() or something ?
     * 
     * @param ObjectManager $objectManager
     * @param Array $options
     */
	public function __construct(ObjectManager $objectManager,$options = null)
	{
			
		parent::__construct($this->formName,$options);
		$fieldset = new $this->fieldsetClass($objectManager);
		$this->add($fieldset);
        // how to add email validation after the fact
        $factory = new InputFilter\Factory();
        
        $inputFilter = $factory->createInputFilter(
            [
                'email' => [
                    'allow_empty' => true,
                    'validators' => [
                        /*
                        [
                            'name' => 'NotEmpty',
                            'options' => [
                                'messages'=>['isEmpty'=> 'email address is required']
                            ],
                            'break_chain_on_failure' => true,
                        ],*/
                        [
                            'name' => 'Zend\Validator\EmailAddress',
                            'options' => [
                                'messages' => [
                                    \Zend\Validator\EmailAddress::INVALID_FORMAT =>
                                        'well-formed email address is required'
                                ],
                            ],
                            //'break_chain_on_failure' => true,
                        ],
                    ],
                    'filters' => [
                        ['name' => 'StringTrim'],
                    ],
                ],
            ]
        );
        $this->getInputFilter()->get('person')->merge($inputFilter);
        $this->add(new Csrf('csrf'));
	}
}

/*
    // does not work:
    $input = $this->getInputFilter()->get('person')->get('email');
    $input->getValidatorChain()->prependByName('NotEmpty', [
        'allow_empty' => false,
        'break_chain_on_failure'=> true,
        'messages'=>['isEmpty'=> 'email is required']]
    );
*/