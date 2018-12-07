<?php
/**  module/Requests/src/Form/ConfigForm.php */

namespace InterpretersOffice\Requests\Form;

use Zend\Stdlib\ArrayObject;
use Zend\Hydrator\ArraySerializable;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Form\Form;



/**
 * configure Requests module -- work in progress
 */
class ConfigForm extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('config-form');
        $this->init();
    }

    public $default_values;

    public function init()
    {
        $json = file_get_contents('module/Requests/config/default.event-listeners.json');

        $data  = json_decode($json,true);
        $this->default_values = $data;
        $this->setHydrator(new ArraySerializable());
        $this->setAllowedObjectBindingClass(ArrayObject::class);
        foreach ($data as $event_name => $event_array) {
            $fieldset = new ConfigFieldset($event_name,['config'=>$event_array]);
            $this->add($fieldset);
        }

    }

    public function getInputFilterSpecification()
    {

        return [];

    }
}
/*
// garbage dump to be cleaned up later

foreach ($data as $event_name => $event_array) {
    foreach ($event_array as $event_type => $event_type_array) {
        foreach ($event_type_array as $language => $action_array) {
            foreach ($action_array as $action => $flag) {
                $id = "$event_name.$event_type.$language.$action";
                $element_name = "{$event_name}[{$event_type}.{$language}.{$action}]";
                $attributes = [
                    'class'=> 'form-check-input',
                    'id'   => "$event_name.$event_type.$language.$action",
                    //'checked' => 'checked',
                ];
                if ($flag) {
                    $attributes['checked'] = 'checked';
                }
                $this->add([
                    'type' => 'checkbox',
                    'name' => $element_name,
                    'attributes' => $attributes,
                    'options' => [
                        'label' => str_replace('-',' ',$action),
                        'value' => $flag,
                        'use_hidden_element' => true,
                        'checked_value' => 1,
                        'unchecked_value' => 0,
                    ],
                ]);
            }
        }
    }
}
*/
