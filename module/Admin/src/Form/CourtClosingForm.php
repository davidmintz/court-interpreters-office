<?php
/** module/Admin/src/Form/CourtClosingForm.php  */
namespace InterpretersOffice\Admin\Form;

use Zend\Form\Form as ZendForm;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Form\CsrfElementCreationTrait;

//use Zend\EventManager\ListenerAggregateInterface;
//use Zend\EventManager\ListenerAggregateTrait;
//use Zend\EventManager\EventManagerInterface;
//use Zend\EventManager\EventInterface;

use Zend\InputFilter\InputFilterProviderInterface;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use InterpretersOffice\Form\ObjectManagerAwareTrait;

use InterpretersOffice\Entity;

class CourtClosingForm extends ZendForm
{
    use CsrfElementCreationTrait;
    use ObjectManagerAwareTrait;

    protected $formName = 'court-closing';

    /**
    * constructor.
    *
    * @param ObjectManager $objectManager
    * @param array         $options
    */
   public function __construct(ObjectManager $objectManager, $options = null)
   {
       parent::__construct($this->formName, $options);
       $this->setObjectManager($objectManager);
       $this->setHydrator(new DoctrineHydrator($objectManager, true));
       $this->addHolidayElement();
       $this->add(
           [
               'type' => 'text',
               'name' => 'id',
               'attributes' => [
                   'id' => 'id'
               ],
           ]
       );
       $this->add(
           [
               'type' => 'text',
               'name' => 'description_other',
               'attributes' => [
                   'id' => 'description_other',
               ],
           ]
       );
       $this->addCsrfElement('court_closing_csrf');
   }

   protected function addHolidayElement()
   {
       $value_options = $this->objectManager
        ->getRepository(Entity\CourtClosing::class)
        ->getHolidays();

       $this->add([
           'type' => 'Select',//Element\Select::class,
           'name' => 'holiday',
           'options' => [
               'label' => 'holiday',
               'value_options' => $value_options,
           ],
           'attributes' => [
               'class' => 'form-control custom-select',
               'id' => 'holiday',
           ],
       ]);

   }

}
