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

    /**
     * makes an element not required
     *
     * the witty name is a little joke. get it?
     * 
     * @param  string $element_name  name of the element
     * @param  string $fieldset_name name of the fieldset
     * @return self
     */
    public function deregulate($element_name = 'hat',$fieldset_name = null)
    {
        if ($fieldset_name) {
            $input = $this->getInputFilter()->get($fieldset_name)
                ->get($element_name);
        } else {
            $input =  $this->getInputFilter()->get($element_name);
        }
        $input->setRequired(false);

        return $this;

    }
}
