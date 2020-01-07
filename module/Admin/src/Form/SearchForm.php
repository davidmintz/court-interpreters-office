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
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'eventType',
            'options' => [
                'label' => 'event type',
                'value_options' => $value_options,
            ],
            'attributes' => ['class' => 'custom-select', 'id' => 'event_type'],
            ]
        );
        $this->add(
            [
            'type' => 'Laminas\Form\Element\Hidden',
            'name' => 'interpreter_id',
            'attributes' => ['id' => 'interpreter_id'],
            ]
        );
        $this->add(
            [
            'type' => 'Laminas\Form\Element\Text',
            'name' => 'interpreter',
            'attributes' => ['id' => 'interpreter',
                'placeholder' => 'last name[, first name]',
                'class'=> 'form-control'],
            ]
        );
        return $this;
    }

    public function getInputFilterSpecification() {

        $spec = parent::getInputFilterSpecification();
        $spec['interpreter_id'] = [
            'required'=>false, 'allow_empty' => true,
        ];
        $spec['interpreter'] = [
            'required'=>false, 'allow_empty' => true,
        ];
        $spec['eventType'] = [
            'required'=>false, 'allow_empty' => true,
        ];

        return $spec;
    }
}
