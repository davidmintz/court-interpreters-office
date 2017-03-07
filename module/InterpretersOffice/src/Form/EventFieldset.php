<?php

/** module/InterpretersOffice/src/Form/EventFieldset.php */

namespace InterpretersOffice\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
//use InterpretersOffice\Form\Validator\NoObjectExists as NoObjectExistsValidator;
//use InterpretersOffice\Form\Validator\UniqueObject;


class EventFieldset extends Fieldset

{
    /**
     * name of the form.
     *
     * @var string
     */
    protected $formName = 'event-form';

    /**
     * name of this Fieldset
     * @var string
     */
    protected $fieldset_name = 'event';
    
    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     * @param array         $options
     */
    public function __construct(ObjectManager $objectManager, Array $options)
    {
        if (!isset($options['action'])) {
            throw new \RuntimeException('missing "action" option in EventFieldset constructor');
        }
        if (!in_array($options['action'], ['create', 'update','repeat'])) {
            throw new \RuntimeException('invalid "action" option in EventFieldset constructor');
        }
        /** might get rid of this... */
        if (isset($options['auth_user_role'])) {
            /** @todo let's not hard-code these roles */
             if (! in_array($options['auth_user_role'],['anonymous','staff','submitter','manager','administrator'])) {
                  throw new \RuntimeException('invalid "auth_user_role" option in Event constructor');
             }
             $this->auth_user_role = $options['auth_user_role'];
        }
        $this->action = $options['action'];
        unset($options['action']);

        parent::__construct($this->fieldset_name, $options);
        $this->objectManager = $objectManager;
        $this->setHydrator(new DoctrineHydrator($objectManager))
                ->setUseAsBaseFieldset(true);
        
        // to be continued: add elements
       
    }
    
}
