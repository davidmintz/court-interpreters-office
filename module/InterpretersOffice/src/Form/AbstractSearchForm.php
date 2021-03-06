<?php /** module/InterpretersOffice/src/Form/SearchForm.php */
namespace InterpretersOffice\Form;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use InterpretersOffice\Form\Element\LanguageSelect;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use InterpretersOffice\Service\ObjectManagerAwareTrait;
use InterpretersOffice\Admin\Form\Validator;
use InterpretersOffice\Admin\Form\Filter;
use InterpretersOffice\Entity;
use Doctrine\Common\Persistence\ObjectManager;
use Laminas\Validator\Callback;

/**
 * abstract search form
 */
class AbstractSearchForm extends Form implements InputFilterProviderInterface, ObjectManagerAwareInterface
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

        $this->setObjectManager($objectManager);
        parent::__construct('search-form', $options);
        $this->add([
            'name' => 'submit',
            'type' => 'Laminas\Form\Element\Hidden',
            'attributes' => [
                'value' => 1,
            ],
        ]);
        $this->getInputFilter()->get('submit')->getValidatorChain()
        ->attachByName('Callback', [
            'callback' => function ($value, $context) {
                unset($context['submit']);
                if (isset($context['order'])) {
                    unset($context['order']);
                }
                foreach ($context as $field => $value) {
                    if (trim($value)) {
                        return true;
                    }
                }
                return false;
            },
            'messages' => [
                Callback::INVALID_VALUE => 'Please enter at least one search criterion.',
            ]
        ]);

        $this->add([
            'name' => 'docket',
            'type' => 'Laminas\Form\Element\Text',
            'attributes' => [
                'id' => 'docket',
                'class' => 'docket form-control',
                 //'placeholder' => '(strongly recommended)',
            ],
             'options' => [
                'label' => 'docket',
             ],
        ]);
        $this->add(
            new LanguageSelect(
                'language',
                [
                    'objectManager' => $objectManager,
                    'attributes'  => [
                        'id' => 'language',
                        'class' => 'custom-select'
                    ],
                    'options' => [
                        'label' => 'language',
                        'empty_item_label' => '',
                    ],
                ]
            )
        );
        $this->add([
            'name' => 'date-from',
            'type' => 'Laminas\Form\Element\Text',
            'attributes' => [
                'id' => 'date-from',
                'class' => 'date form-control',
            ],
            //'options' => [ ],
        ]);
        $this->add([
            'name' => 'date-to',
            'type' => 'Laminas\Form\Element\Text',
            'attributes' => [
                'id' => 'date-to',
                'class' => 'date form-control',
            ],
        ]);
        $this->add([
            'name' => 'defendant-name',
            'type' => 'Laminas\Form\Element\Text',
            'attributes' => [
                'id' => 'defendant-name',
                'class' => 'form-control',
                'placeholder' => 'last name[, first name]'
            ],
        ]);
        /** @var $repository \InterpretersOffice\Entity\Repository\JudgeRepository */
        $repository = $this->getObjectManager()->getRepository(Entity\Judge::class);
        $opts = ['include_pseudo_judges' => true, 'include_inactive' => true,];
        $value_options = $repository->getJudgeOptions($opts);
        array_unshift(
            $value_options,
            [ 'value' => '','attributes' => ['label' => ' '] ]
        );
        $this->add([
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'judge',
            'options' => [
                'label' => 'judge',
                'value_options' => $value_options,
            ],
            'attributes' => [
                'class' => 'form-control custom-select',
                'id' => 'judge'],
        ]);
        $this->add([
            'type' => 'Laminas\Form\Element\Hidden',
            'name' => 'pseudo_judge',
            'attributes' => [
                'id' => 'pseudo_judge'],
        ]);
        if (isset($options['user'])) {
            $user = $options['user'];
            if ($user->judge_ids && 1 == count($user->judge_ids)) {
                $default_judge = $user->judge_ids[0];
                $this->get('judge')->setValue($default_judge);
            }
        }

        $this->add([
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'order',
            'options' => [
                'label' => 'sort by',
                'value_options' => [
                    'desc' => 'latest first',
                    'asc' => 'oldest first',
                ]
            ],
            'attributes' => [
                'class' => 'form-control custom-select',
                'id' => 'order'],
        ]);
        $this->init();
    }

    /**
     * gets inputfilter specification
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            'docket' => [
                'required' => true,
                'allow_empty' => true,
                'filters' => [
                    ['name' => Filter\Docket::class,],
                ],
                'validators' => [
                    [ 'name' => Validator\Docket::class, ]
                ],
            ],
            'language' => [
                'required' => false,
                'allow_empty' => true,
            ],
            'date-from' => [
                'required' => false,
                'allow_empty' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
            ],
            'date-to' => [
                'required' => false,
                'allow_empty' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
            ],
            'defendant-name' => [
                'required' => false,
                'allow_empty' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
            ],
            'judge' => [
                'required' => false,
                'allow_empty' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
            ],
            'pseudo_judge' => [
                'required' => false,
                'allow_empty' => true,
                'filters' => [
                    // @todo a Boolean filter?
                    ['name' => 'StringTrim'],
                ],
            ],
            'order' => [
                'required' => false,
                'allow_empty' => true,
            ],
            'submit' => [
                'required' => true,
                'allow_empty' => false,
            ]
        ];
    }
}
