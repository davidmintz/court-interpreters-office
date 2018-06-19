<?php /** module/Admin/src/Form/DefendantEventFieldset.php */

namespace InterpretersOffice\Admin\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use InterpretersOffice\Form\ObjectManagerAwareTrait;
use InterpretersOffice\Entity;

/**
 * fieldset for defendant names attached to an Event
 */
class DefendantsEventsFieldset extends Fieldset
    implements InputFilterProviderInterface
{
    use ObjectManagerAwareTrait;

    /**
     * constructor
     *
     * @param ObjectManager $objectManager
     * @param array $options
     */
    public function __construct(ObjectManager $objectManager, array $options = [])
    {

        parent::__construct('defendantsEvents', $options);
        $this->objectManager = $objectManager;
        $this->setHydrator(new DoctrineHydrator($objectManager));
        $this->setObject(new Entity\DefendantEvent());
        $this->options = $options;

        $this->add(
            [
                'type' => 'hidden',
                'name' => 'defendant'
            ]
        );
        $this->add(
            [
                'type' => 'hidden',
                'name' => 'event'
            ]
        );
    }

    /**
     * implements InputFilterProviderInterface
     *
     * @todo complete it
     * @return array
     */
    public function getInputFilterSpecification()
    {
 //echo "Hello???? from ".__METHOD__. "....<br>";
        return [];
    }
}
