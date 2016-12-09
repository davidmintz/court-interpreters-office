<?php

/** module/InterpretersOffice/src/Form/Hat.php */

namespace InterpretersOffice\Form;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;

/**
 * form for "Hat" entity. still incomplete.
 */
class Hat extends Form
{
    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $fieldset = new TestFieldset($objectManager);
        parent::__construct('test-form');

        // The form will hydrate an object of type "Hat"
        $this->setHydrator(new DoctrineHydrator($objectManager));

        // Add the fieldset, and set it as the base fieldset
        $fieldset = new TestFieldset($objectManager);
        $fieldset->setUseAsBaseFieldset(true);
        $this->add($fieldset);
    }
}
