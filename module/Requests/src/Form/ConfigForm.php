<?php
/**  module/Requests/src/Form/ConfigForm.php */

namespace InterpretersOffice\Requests\Form;

use Zend\Stdlib\ArrayObject;
use Zend\Hydrator\ArraySerializable;
use Zend\Form\Form;

/**
 * configure Requests module
 */
class ConfigForm extends Form
{
    public function __construct()
    {
        $this->setHydrator(new ArraySerializable());
        $this->setObject(new ArrayObject([]));
        parent::__construct('config-form');
        $this->init();
    }

    public function init()
    {
        

    }
}
