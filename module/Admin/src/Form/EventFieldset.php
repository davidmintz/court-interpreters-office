<?php

/** module/Admin/src/Form/EventFieldset.php */

namespace InterpretersOffice\Admin\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use InterpretersOffice\Form\ObjectManagerAwareTrait;
use InterpretersOffice\Form\Element\LanguageSelect;
use DoctrineModule\Form\Element\ObjectSelect;

use InterpretersOffice\Entity\Judge;
/**
 * Fieldset for Event form
 *
 */
class EventFieldset extends Fieldset implements InputFilterProviderInterface, ObjectManagerAwareInterface
{

     use ObjectManagerAwareTrait;

    /**
     * name of the form.
     *
     * @var string
     */
    protected $formName = 'event-form';

    /**
     * name of this Fieldset
     * @var string
     */
    protected $fieldset_name = 'event';

    /**
     * current user's role
     *
     * @var string
     */
    protected $auth_user_role;  
    
    /**
     * fieldset elements
     * 
     * @var Array some of our element definitions
     */
    protected $elements = [

        [
             'name' => 'date',
            //'type' => 'text',
            'type' => 'Zend\Form\Element\Date',
            'attributes' => [
                'id' => 'date',
                'class' => 'date form-control',
            ],
             'options' => [
                'label' => 'date',
                //'format' => 'm/d/Y',
                'format' => 'Y-m-d',
             ],
        ],
        [
            'name' => 'time',
            //'type' => 'text',
            'type' => 'Zend\Form\Element\Time',
            'attributes' => [
                'id' => 'time',
                'class' => 'time form-control',
            ],
             'options' => [
                'label' => 'time',
                'format' => 'H:i:s',// :s
             ],
        ],
        [
            'name' => 'docket',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => [
                'id' => 'docket',
                'class' => 'docket form-control',
            ],
             'options' => [
                'label' => 'docket',                
            ],            
        ]

    ];


    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array         $options
     */
    public function __construct(ObjectManager $objectManager, array $options)
    {
        if (! isset($options['action'])) {
            throw new \RuntimeException('missing "action" option in EventFieldset constructor');
        }
        if (! in_array($options['action'], ['create', 'update','repeat'])) {
            throw new \RuntimeException('invalid "action" option in EventFieldset constructor: '.(string)$options['action']);
        }
        /** might get rid of this... */
        if (isset($options['auth_user_role'])) {
            /** @todo let's not hard-code these roles */
            if (! in_array($options['auth_user_role'], ['anonymous','staff','submitter','manager','administrator'])) {
                throw new \RuntimeException('invalid "auth_user_role" option in Event fieldset constructor');
            }
            $this->auth_user_role = $options['auth_user_role'];
        }
        $this->action = $options['action'];
        unset($options['action']);

        parent::__construct($this->fieldset_name, $options);
        $this->objectManager = $objectManager;
        $this->setHydrator(new DoctrineHydrator($objectManager))
                ->setUseAsBaseFieldset(true);
        

        foreach ($this->elements as $element) {
            $this->add($element);
        }
        $this->add(
            new LanguageSelect(
                'language',
                [
                    'objectManager' => $objectManager,
                    'attributes'  => [
                        'id' => 'language',
                    ],
                    'options' => [
                        'label' => 'language', 
                        'empty_item_label' => '',
                    ],    
                ]
            )
        );
        
        $this->addJudgeElement();


    }
   

    public function addJudgeElement()
    {
        $element = new ObjectSelect('judge',
            [
                'object_manager' => $this->objectManager,
                'target_class' => 'InterpretersOffice\Entity\Judge',
                'label' => 'judge',
                'label_generator' => function(Judge $judge) {
                    $label = sprintf("%s, %s",$judge->getLastname(),$judge->getFirstname());
                    $middle = $judge->getMiddleName();
                    if ($middle) {
                         if (2 == strlen($middle) && '.' == $middle[1]) {
                            $label .= " $middle";
                        } else {
                            $label .= " $middle[0].";
                        }
                    }
                    $label .= ', '.$judge->getFlavor();
                    return $label;
                },
                'find_method' => ['name'=> 'findAllActive',]
            ]
        );
       $element->setAttributes([ 'class' => 'form-control','id' => 'role',]);
       // $element->getValueOptions() : array of arrays containing keys: label, value, attributes => array
       // we need to jam the generic Magistrate etc in there and sort
       $this->add($element);
    }

    /**
     * implements InputFilterProviderInterface
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        // to be continued
        return [];
    }
}
