<?php /** module/Admin/src/Form/InterpretersAssignedFieldset.php */
namespace InterpretersOffice\Admin\Form;

use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use InterpretersOffice\Service\ObjectManagerAwareTrait;
use InterpretersOffice\Entity;

/**
 * fieldset for Interpreters assigned to an Event
 */
class InterpreterEventsFieldset extends Fieldset implements InputFilterProviderInterface
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

        parent::__construct('interpreterEvents', $options);
        $this->objectManager = $objectManager;
        $this->setHydrator(new DoctrineHydrator($objectManager));
        $this->setObject(new Entity\InterpreterEvent());
        $this->options = $options;

        $this->add(
            [
                'type' => 'hidden',
                'name' => 'interpreter'
            ]
        );
        $this->add(
            [
                'type' => 'hidden',
                'name' => 'event'
            ]
        );
        $this->add(
            [
                'type' => 'hidden',
                'name' => 'created_by'
            ]
        );
        $this->add(
            [
                'type' => 'hidden',
                'name' => 'created'
            ]
        );
    }

    /**
     * implements InputFilterProviderInterface
     *
     * @todo complete it?
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [];
    }
}
