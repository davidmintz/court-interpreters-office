<?php
namespace InterpretersOffice\Requests\Form;

use InterpretersOffice\Admin\Form\AbstractEventFieldset;
use InterpretersOffice\Form\ObjectManagerAwareTrait;
use InterpretersOffice\Entity;

class RequestFieldset extends AbstractEventFieldset
{

    use ObjectManagerAwareTrait;


    /**
     * name of the form.
     *
     * @var string
     */
    protected $formName = 'request-form';

    /**
     * name of this Fieldset
     * @var string
     */
    protected $fieldset_name = 'request';



    public function addEventTypeElement()
    {
        return $this;
    }

    public function addLocationElements($event = null)
    {

        return $this;
    }

    public function addJudgeElements($event = null)
    {
        
        return $this;
    }

    public function getInputFilterSpecification()
    {
        return $this->inputFilterspec;
    }

}
