<?php /** module/InterpretersOffice/src/Form/SearchForm.php */
namespace InterpretersOffice\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use InterpretersOffice\Form\Element\LanguageSelect;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use InterpretersOffice\Service\ObjectManagerAwareTrait;
use InterpretersOffice\Form\CsrfElementCreationTrait;
use InterpretersOffice\Admin\Form\Validator;
use InterpretersOffice\Admin\Form\Filter;
use Doctrine\Common\Persistence\ObjectManager;

class SearchForm extends Form implements InputFilterProviderInterface, ObjectManagerAwareInterface
{
    use ObjectManagerAwareTrait;
    use CsrfElementCreationTrait;

    public function __construct(ObjectManager $objectManager, array $options = [])
    {

        $this->setObjectManager = $objectManager;
        parent::__construct('search-form',$options);
        $this->addCsrfElement();
        $this->add([
            'name' => 'docket',
            'type' => 'Zend\Form\Element\Text',
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
                        'class' => 'custom-select text-muted'
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
            'type' => 'Zend\Form\Element\Text',
            'attributes' => [
                'id' => 'date-from',
                'class' => 'date form-control',
            ],
            //'options' => [ ],
        ]);
        $this->add([
            'name' => 'date-to',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => [
                'id' => 'date-to',
                'class' => 'date form-control',
            ],
            //'options' => [ ],
        ]);
        $this->add([
            'name' => 'defendant-name',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => [
                'id' => 'defendant-name',
                'class' => 'form-control',
            ],
            //'options' => [ ],
        ]);
    }

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
        ];
    }
}
