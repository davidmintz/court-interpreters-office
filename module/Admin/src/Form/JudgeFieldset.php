<?php
/**
 * module/Admin/src/Form/JudgeFieldset.php.
 */

namespace InterpretersOffice\Admin\Form;

use InterpretersOffice\Form\PersonFieldset;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Entity\Judge;

/**
 * JudgeFieldset.
 *
 * @author david
 */
class JudgeFieldset extends PersonFieldset
{
    /**
     * name of the fieldset.
     *
     * since we are a subclass of PersonFieldset, this has to be overriden
     *
     * @var string
     */
    protected $fieldset_name = 'judge';

    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array         $options
     */
    public function __construct(ObjectManager $objectManager, $options = [])
    {
        parent::__construct($objectManager, $options);
        if (isset($options['object'])) {
            if (! $options['object'] instanceof Judge) {
                throw new \InvalidArgumentException(sprintf(
                    'object has to be an instance of %s, got: %s',
                    Judge::class,
                    is_object($options['object']) ?
                     get_class($options['object']) : gettype($options['object'])
                ));
            }
            $judge = $options['object'];
            /**
             *  @todo refactor this part? it's a bit confusing
             *  if this Judge entity has a default location set, we examine it
             *  to see whether it's a courthouse or a courtroom
             */
            if ($defaultLocation = $judge->getDefaultLocation()) {
                if ('courtroom' == $defaultLocation->getType()) {
                    // ...then we need to know the courtroom's id to set the
                    // default in the form, and parent courthouse's for the same
                    // purpose
                    $location_id = $defaultLocation->getId();
                    $parent_location_id = $defaultLocation->getParentLocation()->getId();
                } else {
                    // then the default location is a courthouse, so the default
                    // $location_id is the courthouse id.
                    $parent_location_id = $defaultLocation->getId();
                    $location_id = $parent_location_id;
                }
            }
        } else {
            // for getting courtrooms to populate select.
            // if there is no parent courthouse in the database,
            // the getCourtrooms() repository method knows to return an
            // empty array
            $parent_location_id = 0;
        }
        // the following two elements are not properties of the entity,
        // but rather are only for the UI, so they can select the courthouse
        // and the courtroom. a JS event listener will update the
        // "defaultLocation" hidden element, which is a property
        $this->add([
            'name' => 'courthouse',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'options' => [
                'object_manager' => $this->objectManager,
                'target_class' => 'InterpretersOffice\Entity\Location',
                'label' => 'courthouse',
                'find_method' => ['name' => 'getCourthouses'],
                'property' => 'name',
            ],
            'attributes' => [
                'class' => 'form-control',
                'id' => 'courthouse',
            ],
        ]);
        $this->add([
            'name' => 'courtroom',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'options' => [
                'object_manager' => $this->objectManager,
                'target_class' => 'InterpretersOffice\Entity\Location',
                'label' => 'courtroom',
                'find_method' => [
                    'name' => 'getCourtrooms',
                    'params' => ['parent_id' => $parent_location_id],
                 ],
                'property' => 'name',
            ],
            'attributes' => [
                'class' => 'form-control',
                'id' => 'courtroom',
            ],
        ]);

        $this->add([

            'name' => 'defaultLocation',
            'type' => 'Zend\Form\Element\Hidden',
            'required' => true,
            'allow_empty' => true,
            'attributes' => [
                'id' => 'defaultLocation',
            ],
        ]);

        // this is to make the HTML5 validator happy: a non-empty label attribute
        // for the empty option
        foreach (['courthouse', 'courtroom'] as $elementName) {
            $element = $this->get($elementName);
            $valueOptions = $element->getValueOptions();
            array_unshift($valueOptions, [
               'label' => ' ',
               'value' => '',
               'attributes' => [
                   'label' => ' ',
               ],
            ]);
            $element->setValueOptions($valueOptions);
        }
        $this->get('courthouse')->setValue($parent_location_id);

        if ($options['action'] == 'update' && $location_id != $parent_location_id) {
            $this->get('courtroom')->setValue($location_id);
        }

        // add the judge "flavor" element. this should be made immutable
        // if there's a data history.
        // formerly a DoctrineModule\Form\Element\ObjectSelect, we're changing
        // it so we can cache results without having to write a custom repository
        $this->add([
            //'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'type' => 'Zend\Form\Element\Select',
            'name' => 'flavor',
            'options' => [
                'value_options' => $this->getJudgeFlavorOptions(),
                //'object_manager' => $this->objectManager,
                //'target_class' => 'InterpretersOffice\Entity\JudgeFlavor',
                //'property' => 'flavor',
                'label' => 'flavor',
            ],
             'attributes' => [
                'class' => 'form-control',
                'id' => 'flavor',
             ],
        ]);
        // hack designed to please HTML5 validator
        /*
        $element = $this->get('flavor');
        $options = $element->getValueOptions();
        array_unshift($options, [
           'label' => ' ',
           'value' => '',
           'attributes' => [
               'label' => ' ',
           ],
        ]);
        $element->setValueOptions($options);
`       */
    }

    /**
     * gets the available judge "flavors' for the select menu
     * @return array
     */
    public function getJudgeFlavorOptions()
    {
         $flavors = $this->objectManager
                ->createQuery('SELECT f.id,f.flavor FROM InterpretersOffice\Entity\JudgeFlavor f ORDER BY f.flavor')
                ->useResultCache(true)
                ->getResult();
        $options  [''] = ' ';
        foreach ($flavors as $f) {
            $options[$f['id']] = $f['flavor'];
        }
        return $options;
    }

    /**
     * adds the "Hat" element to our form.
     *
     * @return JudgeFieldset
     */
    public function addHatElement()
    {

        // the Hat is not up for discussion; it has to be Judge.
        // so, we use no select menu. however, might there be a better
        // solution, e.g., an entity listener?
        $this->add(
            [
                'name' => 'hat',
                'type' => 'Zend\Form\Element\Hidden',
                'attributes' => ['id' => 'hat'],
            ]
        );
        return $this;
    }

    /**
     * gets input filter specification.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        $spec = parent::getInputFilterSpecification();
        $spec['flavor'] = [
            'required' => true,
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => [
                            'isEmpty' => 'judge "flavor" is required',
                        ],
                    ],
                ],
            ],
        ];

        $spec['defaultLocation'] = [
            'required' => false,
            'allow_empty' => true,
        ];
        $spec['courthouse'] = [
            'required' => false,
            'allow_empty' => true,
        ];
        $spec['courtroom'] = [
            'required' => false,
            'allow_empty' => true,
        ];
        if (key_exists('mobilePhone', $spec)) {
            unset($spec['mobilePhone']);
        }

        return $spec;
    }
}
