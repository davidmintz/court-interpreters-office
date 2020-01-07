<?php
/** module/InterpretersOffice/src/Form/DateTimeElementFilterTrait.php*/

namespace InterpretersOffice\Form;

use Laminas\Filter\Word\UnderscoreToCamelCase;

/**
 * Trait for removing date/time input elements whose values have
 * not changed
 *
 */
trait DateTimeElementFilterTrait
{
    /**
     * Removes datetime elements if their values have not been modified.
     *
     * This works around a rather annoying characteristic of Doctrine where
     * it runs an update on entities that have DateTime fields, no matter whether
     * their values have really been modified. Presumably it looks at whether
     * the before-and-after objects are identical, as opposed to equivalent.
     *
     * @param  array  $fieldNames   form element names to test
     * @param  array $input         array of input data
     * @param  string $fieldsetName name of fieldset
     * @return \Laminas\Form\Form      or subclass thereof
     * @todo maybe make date/time formats settable, whether as an additional
     * option to pass in, or as a member variable. currently they are hard-coded.
     */
    public function filterDateTimeFields(array $fieldNames, $input, $fieldsetName)
    {
        $filter = new UnderscoreToCamelCase();
        foreach ($fieldNames as $prop) {
            if (strstr($prop, 'time')) {
                $format = 'g:i a';
            } else {
                $format = 'm/d/Y';
            }
            $method = 'get'.ucfirst($filter->filter($prop));
            $datetime = $this->getObject()->$method();
            if (! $datetime) {
                continue;
            }
            if (! isset($input[$prop])) {
                continue;
            }
            if ($datetime->format($format) == $input[$prop]) {
                unset($input[$prop]);
                $this->getInputFilter()->get($fieldsetName)->remove($prop);
            }
        }

        return $this;
    }
}
