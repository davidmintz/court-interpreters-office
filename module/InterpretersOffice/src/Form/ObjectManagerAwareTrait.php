<?php
/**
 * module/InterpretersOffice/src/Form/ObjectManagerAwareTrait.php
 */

namespace InterpretersOffice\Form;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * automates implementation of Doctrine ObjectManagerAwareInterface
 * 
 * @see DoctrineModule\Persistence\ObjectManagerAwareInterface
 */
trait ObjectManagerAwareTrait {

    /**
     * sets ObjectManager instance
     * 
     * @var ObjectManager
     */
    protected $objectManager;
    
    /**
     * sets ObjectManager
     * 
     * @param ObjectManager
     */
    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }
    /**
     * gets ObjectManager instance
     * 
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }
}
