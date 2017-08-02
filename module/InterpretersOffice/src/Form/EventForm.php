<?php
/** module/InterpretersOffice/src/Form/EventForm.php */

namespace InterpretersOffice\Form;

use Zend\Form\Form as ZendForm;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * form for Event entity
 *
 */
class EventForm extends ZendForm
{

     use CsrfElementCreationTrait;

     /**
     * name of Fieldset class to instantiate and add to the form.
     *
     * subclasses can override this with the classname
     * of a Fieldset that extends EventFieldset
     *
     * @var string
     */
    protected $fieldsetClass = EventFieldset::class;

    /**
     * name of the form
     *
     * @var string
     */
    protected $formName = 'event-form';

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
