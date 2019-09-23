<?php /** module/Admin/src/Form/SearchForm.php */

namespace InterpretersOffice\Admin\Form;

use InterpretersOffice\Form\AbstractSearchForm;
use InterpretersOffice\Entity;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * search form for admin users
 */
class SearchForm extends AbstractSearchForm
{
    /**
     * adds more elements
     *
     * @return SearchForm
     */
    public function init()
    {
        // add more elements!
        $repo = $this->objectManager->getRepository(Entity\EventType::class);
        $value_options = array_merge(
            [
                  ['label' => ' ','value' => '',]
                ],
            $repo->getEventTypeOptions()
        );
        $this->add(
            [
            'type' => 'Zend\Form\Element\Select',
            'name' => 'eventType',
            'options' => [
                'label' => 'event type',
                'value_options' => $value_options,
            ],
            'attributes' => ['class' => 'custom-select', 'id' => 'event_type'],
            ]
        );
        return $this;
    }

}
