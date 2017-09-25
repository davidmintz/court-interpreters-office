<?php
/** module/InterpretersOffice/src/Form/Element/LanguageSelect.php */

namespace InterpretersOffice\Form\Element;

use DoctrineModule\Form\Element\ObjectSelect;

/**
 * specialized form element for selecting a language
 */
class LanguageSelect extends ObjectSelect
{

    /**
     * default options for the element
     *
     * @var array
     */
    protected $defaultOptions = [
        'name' => 'language-select',
        'options' => [
            'target_class' => 'InterpretersOffice\Entity\Language',
            'property' => 'name',
            'label' => 'languages',
            'empty_item_label'   => '--- select language --',
        ],
        'attributes' => [
            'class' => 'form-control',
            'id' => 'language-select',
        ],
    ];

    /**
     * constructor
     *
     * @param string $name element name
     * @param array $options
     */
    public function __construct($name = null, array $options)
    {
        $this->getProxy()->setObjectManager($options['objectManager']);
        if (isset($options['options'])) {          
            $constructor_options = array_merge($this->defaultOptions['options'],  $options['options']);
        } else {
            $constructor_options = $this->defaultOptions['options'];
        }
        parent::__construct($name,$constructor_options);
        $attributes = $this->defaultOptions['attributes'];
        if (key_exists('attributes', $options)) {
            $attributes = array_merge($attributes, $options['attributes']);
        }
        $this->setAttributes($attributes);
        $select_options = $this->getValueOptions();

        array_unshift($select_options, [
            'label' => $this->getOption('empty_item_label'),
            'value' => '',
            'attributes' => ['label' => ' '],
        ]);
        $this->setValueOptions($select_options);
    }
}
