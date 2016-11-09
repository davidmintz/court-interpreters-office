<?php

/** module/Application/src/Form/TestPersonFieldset.php */

namespace Application\Form;

use Zend\Form\Fieldset;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * scribble for testing and experimenting.
 */
class TestPersonFieldset extends Fieldset
{
    /** @var ObjectManager */
    protected $objectManager;
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
        echo __METHOD__,'<br>';
    }
    public function init()
    {
        echo __METHOD__,'<br>';
    }
    public function getInputFilterSpecification()
    {
        echo __METHOD__.' is running!<br>';

        return [];
    }

    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function getObjectManager()
    {
        return $this->objectManager;
    }
}
