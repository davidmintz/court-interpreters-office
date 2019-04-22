<?php

/** module/InterpretersOffice/src/Form/HatFieldset.php */

namespace InterpretersOffice\Form;

use Zend\Form\Fieldset;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use InterpretersOffice\Entity;
use InterpretersOffice\Service\ObjectManagerAwareTrait;

/**
 * Fieldset for the Hat entity, yet to be completed.
 */
class HatFieldset extends Fieldset implements ObjectManagerAwareInterface
{
    use ObjectManagerAwareTrait;

    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param type array
     */
    public function __construct(ObjectManager $objectManager, $options = [])
    {
        parent::__construct($options);
        $this->setHydrator(new DoctrineHydrator($objectManager))
        ->setObject(new Entity\Hat());
        $this->add(
            [
            'type' => 'Zend\Form\Element\Text',
            'name' => 'name',
            'attributes' => ['id' => 'name'],
             'options' => ['label' => 'Name of Hat'],
            ]
        );

        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'anonymous',
            'attributes' => ['id' => 'anonymous'],
            'options' => ['label' => 'allow anonymous?'],
            ]);
        $this->objectManager = $objectManager;
    }
}
