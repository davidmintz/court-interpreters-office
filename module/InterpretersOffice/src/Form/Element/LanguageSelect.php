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
        $this->getProxy()->setTargetClass('InterpretersOffice\Entity\Language');
        parent::__construct($name, $options);
        $attributes = $this->defaultOptions['attributes'];
        if (key_exists('attributes', $options)) {
            $attributes = array_merge($attributes, $options['attributes']);
        }
        $this->setAttributes($attributes);
        $select_options = $this->getValueOptions();
        array_unshift($select_options, [
            'label' => '-- select a language --',
            'value' => '',
            'attributes' => ['label' => ' '],
        ]);
        $this->setValueOptions($select_options);
    }
}
