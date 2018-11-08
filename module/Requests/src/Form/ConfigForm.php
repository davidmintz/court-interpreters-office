<?php
/**  module/Requests/src/Form/ConfigForm.php */

namespace InterpretersOffice\Requests\Form;

use Zend\Stdlib\ArrayObject;
use Zend\Hydrator\ArraySerializable;
use Zend\Form\Form;

/**
 * configure Requests module
 */
class ConfigForm extends Form
{
    public function __construct()
    {
        $this->setHydrator(new ArraySerializable());
        $this->setObject(new ArrayObject([]));
        parent::__construct('config-form');
        $this->init();
    }

    public function init()
    {
        $json = file_get_contents('module/Requests/config/default-event-handlers.config.json');
        $data  = json_decode($json,true);
        foreach ($data as $event_name => $event_array) {
            foreach ($event_array as $event_type => $event_type_array) {
                foreach ($event_type_array as $language => $action_array) {
                    foreach ($action_array as $action => $flag) {
                        $id = "$event_name.$event_type.$language.$action";
                        $element_name = "{$event_name}[{$event_type}][{$language}][{$action}]";
                        $this->add([
                            'type' => 'checkbox',
                            'name' => $element_name,
                            //'value' => $flag,
                            'attributes' => [
                                'class'=> 'form-check-input',
                                'id'   => "$event_name.$event_type.$language.$action",
                            ],
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
