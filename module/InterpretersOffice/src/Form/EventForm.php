<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace InterpretersOffice\Form;

use Zend\Form\Form as ZendForm;
//use Zend\InputFilter\InputFilterProviderInterface;
//use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Description of EventForm
 *
 * @author david
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
