<?php
/**  module/Requests/src/Form/ConfigFieldset.php */

namespace InterpretersOffice\Requests\Form;

use Zend\Stdlib\ArrayObject;
use Zend\Hydrator\ArraySerializable;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Form\Fieldset;


class ConfigFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);
        $config = $options['config'];
        foreach ($config as $name => $value) {
            $label = explode('.',$name)[2];
            $this->add(
                [
                    'type' => 'checkbox',
                    'name' => $name,
                    'attributes' => [
                        'class'=> 'form-check-input',
                        'id'   => $name,
                    ],
                    'options' => [
                        'label' => str_replace('-',' ',$label),
                        //'value' => $value,
                        'use_hidden_element' => true,
                        'checked_value' => 1,
                        'unchecked_value' => 0,
                    ],
                ]
            );
        }
    }
    public function getInputFilterSpecification()
    {
        return [];
    }
}
