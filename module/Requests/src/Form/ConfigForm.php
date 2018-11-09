<?php
/**  module/Requests/src/Form/ConfigForm.php */

namespace InterpretersOffice\Requests\Form;

use Zend\Stdlib\ArrayObject;
use Zend\Hydrator\ArraySerializable;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Form\Form;
use Zend\Form\Fieldset;

/**
 * configure Requests module
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
        $json = file_get_contents('module/Requests/config/default-event-handlers.config.json');
        $data  = json_decode($json,true);
        $this->default_values = $data;
        $this->setHydrator(new ArraySerializable());
        $this->setAllowedObjectBindingClass(ArrayObject::class);
        //$fieldset_1 = new Fieldset('event-name');
        //echo "hi there...";
        foreach ($data as $event_name => $event_array) {
            foreach ($event_array as $event_type => $event_type_array) {
                foreach ($event_type_array as $language => $action_array) {
                    foreach ($action_array as $action => $flag) {
                        $id = "$event_name.$event_type.$language.$action";
                        $element_name = "{$event_name}[{$event_type}][{$language}][{$action}]";
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


        //$this->defaults = new ArrayObject($data);
    }

    public function getInputFilterSpecification()
    {

        return [

            'cancel'=> [
                'spanish' => [
                    'in-court' => [
                        'delete-scheduled-event' => [
                            'required' => true,
                            'allow_empty' => true,
                        ],
                    ]
                ]
            ]
        ];
    }
}
/*
'type' => Element\Checkbox::class,
    'name' => 'checkbox',
    'options' => [
        'label' => 'A checkbox',
        'use_hidden_element' => true,
        'checked_value' => 'yes',
        'unchecked_value' => 'no',
    ],
    'attributes' => [
         'value' => 'yes',
    ],
*/

/*
[cancel] => Array
        (
            [in-court] => Array
                (
                    [spanish] => Array
                        (
                            [delete-scheduled-event] => 1
                            [notify-assigned-interpreters] => 0
                        )

                )

            [out-of-court] => Array
                (
                    [spanish] => Array
                        (
                            [delete-scheduled-event] => 1
                            [notify-assigned-interpreters] => 1
                        )

                    [non-spanish] => Array
                        (
                            [delete-scheduled-event] => 1
                            [notify-assigned-interpreters] => 1
                        )

                )

        )

*/
