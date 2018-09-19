<?php
namespace InterpretersOffice\Requests\Form;

use InterpretersOffice\Admin\Form\AbstractEventFieldset;
use InterpretersOffice\Form\ObjectManagerAwareTrait;
use InterpretersOffice\Entity;
use InterpretersOffice\Entity\EventType;
use InterpretersOffice\Entity\Repository\LocationRepository;

use Doctrine\Common\Persistence\ObjectManager;


class RequestFieldset extends AbstractEventFieldset
{

    use ObjectManagerAwareTrait;


    /**
     * name of the form.
     *
     * @var string
     */
    protected $formName = 'request-form';

    /**
     * name of this Fieldset
     * @var string
     */
    protected $fieldset_name = 'request';

    /**
     * Object manager
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * constructor
     */
    public function __construct(ObjectManager $objectManager, Array $options)
    {
        parent::__construct($objectManager, $options);
        // sanity check
        if (! key_exists('auth',$options) or ! $options['auth']
        instanceof \Zend\Authentication\AuthenticationServiceInterface ) {
            throw new \Exception(
                "constructor options to RequestFieldset must include 'auth'");
        }

        // (re)set some element attributes
        foreach (['date','time',] as $name) {
            $this->get($name)->setAttribute('placeholder', '');
        }

        $language_element = $this->get('language');
        $opts = $language_element->getValueOptions();
        $opts[0] = ['label' => '','value'=> ''];
        $language_element->setValueOptions($opts);

        $event_type_element = $this->get('eventType');
        $opts = $event_type_element->getValueOptions();
        array_unshift($opts, ['label' => ' ','value'=> '']);
        $event_type_element->setValueOptions($opts);

    }

    /**
     * adds event-type select menu
     *
     * @return RequestFieldset
     */
    public function addEventTypeElement()
    {
        $hat = $this->options['auth']->getIdentity()->hat;
        $repo = $this->objectManager->getRepository(Entity\EventType::class);
        $options = $repo->getEventTypesForHat($hat);
        $this->add(
            [
            'type' => 'Zend\Form\Element\Select',
            'name' => 'eventType',
            'options' => [
                'label' => 'event type',
                'value_options' => $options,
            ],
            'attributes' => ['class' => 'custom-select text-muted', 'id' => 'event-type'],
            ]
        );

        return $this;
    }

    /**
     * adds location select menu
     *
     * @return RequestFieldset
     */
    public function addLocationElements()
    {
        $hat = $this->options['auth']->getIdentity()->hat;
        /** @var \InterpretersOffice\Entity\Repository\LocationRepository  $repo*/
        $repo = $this->objectManager->getRepository(Entity\Location::class);

        $options = $repo->getLocationOptionsForHat($hat);
        $this->add(
            [
            'type' => 'Zend\Form\Element\Select',
            'name' => 'location',
            'options' => [
                'label' => 'location',
                'value_options' => $options,
                /*
                [
                0 =>
                  [
                    'label' => 'European languages',
                    'options' => [
                       '0' => 'French',
                       '1' => 'Italian',
                       ['label'=>"shit",'value'=>"33"]
                    ],
                 ],
                 1 =>
                 [
                    'label' => 'Asian languages',
                    'options' => [
                       '2' => 'Japanese',
                       '3' => 'Chinese',
                    ],
                 ],
             ],
             */

            ],
            'attributes' => ['class' => 'custom-select text-muted', 'id' => 'event-type'],
            ]
        );

        return $this;
    }

    public function addJudgeElements()
    {

        return $this;
    }

    public function getInputFilterSpecification()
    {
        return $this->inputFilterspec;
    }

}
