<?php
/**
 * module/InterpretersOffice/Form/CsrfElementCreationTrait.php.
 */

namespace InterpretersOffice\Form;

use Laminas\Form\Element\Csrf;
use Laminas\InputFilter;

/**
 * trait to facilitate adding a CSRF element to a Form.
 *
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
                        'notSame' => 'Security error: invalid/expired CSRF token.'
                        .' Please reload the page and try again.',
                    ],
                'timeout' => 2700,
            ]
        );
        // it's no use. formHidden() helper evidently doesn't
        // believe in id attribs, but...
        $element->setAttribute('id', $name);
        $this->add($element);
        $input = $this->getInputFilter()->get($name);
        $input->setAllowEmpty(false)->setRequired(true);
        $validator = new \Laminas\Validator\NotEmpty([
                'messages' => ['isEmpty' => "Security error: form is missing CSRF token"],
                'break_chain_on_failure' => true,
            ]);
        $input->getValidatorChain()->attach($validator);

        return $this;
    }
}
