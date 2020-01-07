<?php
/**  module/Admin/src/Form/InterpreterRosterForm.php Interpreter search UI  */

namespace InterpretersOffice\Admin\Form;

use Laminas\Form\Form;
use InterpretersOffice\Form\Element\LanguageSelect;

/**
 * Form for looking up interpreters
 */
class InterpreterRosterForm extends Form
{
    /**
     * constructor
     *
     * the sole required element of $options is
     * 'objectManager' => Doctrine\Common\Persistence\ObjectManager
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        parent::__construct('interpreter-roster', $options);

        $this->add(
            new LanguageSelect(
                'language_id',
                [
                    'objectManager' => $options['objectManager'],
                    'attributes' => [
                        'id' => 'language_id',
                    ],
                    'empty_item_label' => '-- any language --',
                ]
            )
        );

        $this->add(
            [
                 'type' => 'Laminas\Form\Element\Select',
                 'name' => 'active',
                 'options' => [
                    'value_options' => [
                        1 => 'active',
                        0 => 'not active',
                       -1 => 'any status',
                    ],
                 ],

                 'attributes' => [
                     'id' => 'active',
                     'class' => 'form-control',
                 ]
             ]
        );

        $this->add(
            [
                 'type' => 'Laminas\Form\Element\Select',
                 'name' => 'security_clearance_expiration',
                 'options' => [
                    'value_options' => [
                         1 => 'valid',
                         0 => 'expired',
                        -2 => 'none',
                        -1 => 'any status',
                    ],
                 ],
                 'attributes' => [
                     'id' => 'security_clearance_expiration',
                     'class' => 'form-control',
                 ]
             ]
        );
        $this->add(
            [
                 'type' => 'Laminas\Form\Element\Text',
                 'name' => 'name',
                 'attributes' => [
                     'id' => 'name',
                     'class' => 'form-control',
                 ]
             ]
        );
    }
}
