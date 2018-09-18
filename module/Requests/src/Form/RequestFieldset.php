<?php
namespace InterpretersOffice\Requests\Form;

use InterpretersOffice\Admin\Form\AbstractEventFieldset;
use InterpretersOffice\Form\ObjectManagerAwareTrait;
use InterpretersOffice\Entity;
use InterpretersOffice\Entity\EventType;

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
    }

    public function addEventTypeElement()
    {
        $hat = $this->options['auth']->getIdentity()->hat;
        $repo = $this->objectManager->getRepository(Entity\EventType::class);
        $options = $repo->getEventTypesForHat($hat);

        return $this;
    }

    public function addLocationElements()
    {

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
