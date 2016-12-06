<?php

namespace InterpretersOffice\Form;

use Zend\Form\Fieldset;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Element;
use InterpretersOffice\Entity\Hat;

class TestFieldset extends Fieldset implements ObjectManagerAwareInterface
{
    protected $objectManager;

    public function __construct(ObjectManager $objectManager, $options = [])
    {
        parent::__construct($options);
        $this->setHydrator(new DoctrineHydrator($objectManager))
        ->setObject(new Hat());
        $this->add(//new Element(
            [
            'type' => 'Zend\Form\Element\Text',
            'name' => 'name',
            'attributes' => ['id' => 'name'],
             'options' => ['label' => 'Name of Hat'],
            ]
        //)
        );

        $this->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'anonymous',
            'attributes' => ['id' => 'anonymous'],
             'options' => ['label' => 'allow anonymous?'],
            ]
        );

        $this->objectManager = $objectManager;
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
