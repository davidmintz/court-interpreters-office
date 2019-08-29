<?php
/** module/Admin/src/Form/EventForm.php */

namespace InterpretersOffice\Requests\Form;

use InterpretersOffice\Admin\Form\AbstractEventFieldset;
use InterpretersOffice\Service\ObjectManagerAwareTrait;


use Zend\Form\Form as ZendForm;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Form\CsrfElementCreationTrait;
use InterpretersOffice\Form\DateTimeElementFilterTrait;
use  Zend\Mvc\Controller\AbstractActionController;
/**
 * form for Request entity
 *
 */
class RequestForm extends ZendForm
{

     use CsrfElementCreationTrait, DateTimeElementFilterTrait;


     /**
     * name of Fieldset class to instantiate and add to the form.
     *
     *
     * @var string
     */
    protected $fieldsetClass = RequestFieldset::class;

    /**
     * name of the form
     *
     * @var string
     */
    protected $formName = 'request-form';

    /**
     * date/time properties
     *
     * @var array
     */
    protected $datetime_props = ['date','time',];



     /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array         $options
     */
    public function __construct(ObjectManager $objectManager, $options = null)
    {
        parent::__construct($this->formName, $options);
        $fieldset = new $this->fieldsetClass($objectManager, $options);
        $this->add($fieldset);

        if ("update" == $this->options['action']) {
            $this->add([
                'type' => 'Hidden',
                'name' => 'modified',
                'attributes' => ['id' => 'modified'],
            ]);
        }
        $this->addCsrfElement();
    }

    /**
     * moves data around following successful validation
     *
     * @return RequestForm
     */
    public function postValidate(AbstractActionController $controller)
    {
        // subject to reconsideration, the least ugly way to store the
        // names they could|would not find in the database
        $entity = $this->getObject();
        if ($this->get('request')->has('extra_defendants')) {
            $names = $this->get('request')->get('extra_defendants')->getValue();
            if ($names) {
                $entity->setExtraData(['defendants' => $names]);
            } else {
                // if there's anything there, remove it
                if ($entity->getExtraData()) {
                    $entity->setExtraData([]);
                }
            }
        }
        if ($this->options['action'] == 'create') {
            $user =  $controller->getUserEntity();
            $entity->setSubmitter($user->getPerson())
                ->setModifiedBy($user);
        }

        return $this;
    }
}
