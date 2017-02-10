<?php

/** module/InterpretersOffice/src/Form/PersonForm.php */

namespace InterpretersOffice\Form;

use Zend\Form\Form as ZendForm;
//use Zend\InputFilter\InputFilterProviderInterface;
//use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Form for a Person entity.
 */
class PersonForm extends ZendForm //implements ObjectManagerAwareInterface
{
    use CsrfElementCreationTrait;

    /**
     * name of the form.
     *
     * @var string
     */
    protected $formName = 'person-form';

    /**
     * name of Fieldset class to instantiate and add to the form.
     *
     * the idea is that subclasses can override this with the classname 
     * of a Fieldset that extends PersonFieldset
     *
     * @var string
     */
    protected $fieldsetClass = PersonFieldset::class;

    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array         $options
     */
    public function __construct(ObjectManager $objectManager, $options = null)
    {
        parent::__construct($this->formName, $options);
        $fieldset = new $this->fieldsetClass($objectManager, $options);
        $this->add($fieldset);
        $this->addCsrfElement();
    }
}
