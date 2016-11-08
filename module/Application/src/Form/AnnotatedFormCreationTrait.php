<?php
/** module/Application/src/Form/AnnotatedFormCreationTrait.php */

namespace Application\Form;
use Zend\Form\Form;

/**
 * Trait that provides a convenience method for instantiating the 
 * form for adding/editing the entity that the controller manages.
 */
trait AnnotatedFormCreationTrait {
    
    /** @var Form the Zend Form object to be returned */
    protected $form;
    
    /**
     * lazy-instantiates and returns a Form
     * 
     * @param string $className FQCN of the entity corresponding to the form
     * @param array $options
     * @see Factory\FormFactory::__invoke()
     * @return Form 
     */
    function getForm($className,Array $options) {
        
        if (! $this->form) {
            $form = $this->formElementManager->build($className,$options);
            $this->form = $form;
        }
        return $this->form;
    }
}
