<?php /** module/Admin/src/Form/AbstractEventFieldset.php */
namespace InterpretersOffice\Admin\Form;

use Zend\Form\Fieldset;
use Zend\Form\Element;
use Zend\InputFilter\InputFilterProviderInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use InterpretersOffice\Form\ObjectManagerAwareTrait;

use InterpretersOffice\Form\Element\LanguageSelect;
use InterpretersOffice\Entity;

use Zend\Validator\Callback;
use InterpretersOffice\Entity\Judge;
use InterpretersOffice\Entity\Event;
use InterpretersOffice\Entity\Repository\JudgeRepository;

abstract class AbstractEventFieldset extends Fieldset implements
    InputFilterProviderInterface,
    ObjectManagerAwareInterface
{
    /**
      * type to use for time elements
      *
      * for convenience, while trying to make up our mind about
      * using the HTML5 date and time elements
      *
      * @var string
      */
     const TIME_ELEMENT_TYPE = 'Text';
      /**
      * type to use for date elements
      *
      * @var string
      */
     const DATE_ELEMENT_TYPE = 'Text';

     /**
      * current user's role
      *
      * @var string
      */
     protected $auth_user_role;

     /**
      * constructor options
      *
      * @var Array
      */
     protected $options;

     /**
      * action
      *
      * @var string
      */
     protected $action;


     /**
      *  input filter specification.
      *
      *  validation rules that are common to both admin and
      *  submitter form elements
      *  @var array
      */
      protected $inputFilterspec = [
          'id' => [
              'required' => true,
              'allow_empty' => true,
          ],
          'date' => [
              'required' => true,
              'allow_empty' => false,
              'validators' => [
                  [
                      'name' => 'NotEmpty',
                      'options' => [
                          'messages' => [
                              'isEmpty' => 'date is required',
                          ],
                      ],
                      'break_chain_on_failure' => true,
                  ],
              ],
          ],
          'language' => [
              'required' => true,
              'allow_empty' => false,
              'validators' => [
                  [
                      'name' => 'NotEmpty',
                      'options' => [
                          'messages' => [
                              'isEmpty' => 'language is required',
                          ],
                      ],
                  ],
              ],
          ],
          'eventType' => [
              'required' => true,
              'allow_empty' => false,
              'validators' => [
                  [
                      'name' => 'NotEmpty',
                      'options' => [
                          'messages' => [
                              'isEmpty' => 'event-type is required',
                          ],
                      ],
                  ],
              ],
          ],
          'docket' => [
              'required' => true,
              'allow_empty' => true,
              'filters' => [
                  ['name' => Filter\Docket::class,],
              ],
              'validators' => [
                  [ 'name' => Validator\Docket::class, ]
              ],
          ],
          'location' => [
              'required' => false,
               'allow_empty' => true,
               'validators' => [

               ],
          ],
           'parent_location' => [
               'required' => false,
               'allow_empty' => true,
               'validators' => [

               ],
           ],
           'comments' => [
               'required' => false,
               'allow_empty' => true,
               'validators' => [
                   [
                       'name' => 'StringLength',
                       'options' => [
                           'min' => 5,
                           'max' => 600,
                           'messages' => [
                           \Zend\Validator\StringLength::TOO_LONG =>
                               'maximum length allowed is 600 characters',
                            \Zend\Validator\StringLength::TOO_SHORT =>
                               'minimum length allowed is 5 characters',
                           ]
                       ]
                   ]
               ],
               'filters' => [
                   ['name' => 'StringTrim'],
               ],
           ],

      ];

     /**
      * fieldset elements
      *
      * @var Array some of our element definitions
      */
     protected $elements = [
         [
             'name' => 'id',
             'type' => 'Zend\Form\Element\Hidden',
             'attributes' => ['id' => 'event_id'],
         ],

         [
              'name' => 'date',
             //'type' => 'text',
             'type' => self::DATE_ELEMENT_TYPE,
             'attributes' => [
                 'id' => 'date',
                 'class' => 'date form-control',
                 'placeholder' => '(required)',
             ],
              'options' => [
                 'label' => 'date',
                 //'format' => 'm/d/Y',
                 //'format' => 'Y-m-d',
              ],
         ],
         [
             'name' => 'time',
             //'type' => 'text',
             'type' => self::TIME_ELEMENT_TYPE,
             'attributes' => [
                 'id' => 'time',
                 'class' => 'time form-control',
             ],
              'options' => [
                 'label' => 'time',
                //    'format' => 'H:i:s',// :s
              ],
         ],
         [
             'name' => 'docket',
             'type' => 'Zend\Form\Element\Text',
             'attributes' => [
                 'id' => 'docket',
                 'class' => 'docket form-control',
                  'placeholder' => '(strongly recommended)',
             ],
              'options' => [
                 'label' => 'docket',
              ],
         ],
         [
             'name' => 'defendant-search',
             'type' => 'Zend\Form\Element\Text',
             'attributes' => [
                 'id' => 'defendant-search',
                 'class' => 'form-control',
                 'placeholder' => 'last name[, first name]'
             ],
              'options' => [
                 'label' => 'defendants',
              ],
         ],

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
             throw new \RuntimeException(
                 'missing "action" option in constructor'
             );
         }
         if (! in_array($options['action'], ['create', 'update','repeat'])) {
             throw new \RuntimeException('invalid "action" option in '
                 . 'EventFieldset constructor: '.(string)$options['action']);
         }
         if (! isset($options['object'])) {
             $options['object'] = null;
         }
         /** might get rid of this... */
         if (isset($options['auth_user_role'])) {
             /** @todo let's not hard-code these roles */
             if (! in_array(
                 $options['auth_user_role'],
                 ['anonymous','staff','submitter','manager','administrator']
             )) {
                 throw new \RuntimeException(
                     'invalid "auth_user_role" option in Event fieldset constructor'
                 );
             }
             $this->auth_user_role = $options['auth_user_role'];
         }
         $this->action = $options['action'];
         $this->options = $options;
         $this->setObjectManager($objectManager);
         parent::__construct($this->fieldset_name, $options);

         $this->addJudgeElements($options['object'])
             ->addEventTypeElement()
             ->addLocationElements($options['object']);

         $this->add(
             new LanguageSelect(
                 'language',
                 [
                     'objectManager' => $objectManager,
                     'attributes'  => [
                         'id' => 'language',
                         'class' => 'custom-select text-muted'
                     ],
                     'options' => [
                         'label' => 'language',
                         'empty_item_label' => '(required)',
                     ],
                 ]
             )
         );
         $this->objectManager = $objectManager;
         $this->setHydrator(new DoctrineHydrator($objectManager))
                 ->setUseAsBaseFieldset(true);

         foreach ($this->elements as $element) {
             $this->add($element);
         }

     }

     /**
      * adds Judge element(s)
      *
      * //param Entity\Event $event
      * @return AbstractEventFieldset
      */
     abstract public function addJudgeElements();

     /**
      * adds event-type element
      * @return AbstractEventFieldset
      */
     abstract function addEventTypeElement();

     /**
      * adds Location element(s)
      *
      * @param Entity\Event $event
      * @return AbstractEventFieldset
      */
     abstract public function addLocationElements();

}
