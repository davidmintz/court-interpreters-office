<?php

/** module/InterpretersOffice/src/Form/AnnotatedFormCreationTrait.php */

namespace InterpretersOffice\Form;

use Zend\Form\Form;

/**
 * Trait that provides a convenience method for instantiating the
 * form for adding/editing the entity that the controller manages.
 */
trait AnnotatedFormCreationTrait
{
    /** @var Form the Zend Form object to be returned */
    protected $form;

    /**
     * lazy-instantiates and returns a Form.
     *
     * @param string $className FQCN of the entity corresponding to the form
     * @param array  $options
     *
     * @see Factory\FormFactory::createForm()
     *
     * @return Form
     */
    public function getForm($className, array $options)
    {
        if (!$this->form) {
            
            $this->form = $this->formFactory->createForm($className,$options);
            
        }

        return $this->form;
    }
}
