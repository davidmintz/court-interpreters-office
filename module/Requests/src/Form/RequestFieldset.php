<?php
namespace InterpretersOffice\Requests\Form;

use InterpretersOffice\Admin\Form\AbstractEventFieldset;
use InterpretersOffice\Form\ObjectManagerAwareTrait;


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

    public function addLocationElements()
    {

        return $this;
    }

    public function addJudgeElements()
    {

        return $this;
    }

    public function getInputFilterSpecification()
    {
        return $this->inputFilterspec;
    }

}
