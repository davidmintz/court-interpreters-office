<?php
/**
 * module/InterpretersOffice/Form/CsrfElementCreationTrait.php.
 */

namespace InterpretersOffice\Form;

use Zend\Form\Element\Csrf;
use Zend\InputFilter;

/**
 * trait to facilitate adding a CSRF element to a Form.
 *
 * yes, we could create subclasses of Zend\Form\Form and Zend\Form\Fieldset that
 * do this, and extend those instead. but this works, and Traits are cool.
 */
trait CsrfElementCreationTrait
{
    /**
     * adds a CSRF element to the form.
     * @param string $name of the csrf element
     * @return mixed. whatever $this is (Form or Fieldset instance)
     */
    public function addCsrfElement($name = 'csrf')
    {

        $element = new Csrf($name);
        $element->setCsrfValidatorOptions(
            [
                'messages' =>
                    [
                        'notSame' => 'security error: invalid/expired CSRF token',
                    ],
                'timeout' => 600,
            ]
        );
        // it's no use. formHidden() helper evidently doesn't
        // believe in id attribs, but...
        $element->setAttribute('id', $name);
        $this->add($element);
        $input = $this->getInputFilter()->get($name);
        $input->setAllowEmpty(false)->setRequired(true);
        $validator = new \Zend\Validator\NotEmpty([
                'messages' => ['isEmpty' => "security error: form is missing CSRF token"],
                'break_chain_on_failure' => true,
            ]);
        $input->getValidatorChain()->attach($validator);

        return $this;
    }
}
