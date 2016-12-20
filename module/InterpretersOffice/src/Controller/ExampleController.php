<?php
/**
 * module/InterpretersOffice/src/Controller/ExampleController.php.
 */

namespace InterpretersOffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Doctrine\Common\Persistence\ObjectManager;

use InterpretersOffice\Form\PersonForm;

/**
 *  ExampleController.
 *
 *  Currently, just for making sure the application runs, basic routing is
 *  happening, service container is working, views are rendered, etc.
 */
class ExampleController extends AbstractActionController 
{
    
    /** @var ObjectManager */
    protected $objectManager;
    
    /**
     * constructor
     * 
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }
    /**
     * fool around with person form and fieldset
     */
    public function formAction()
    {

        echo "shit works in formAction ... ";

        $form = new PersonForm($this->objectManager);

        $entity = new \InterpretersOffice\Entity\Person;

        $form->bind($entity);

        $form->setData([
            'person-fieldset' => [
                'firstname' => "Wank",
                'lastname'=> "Gackersly", 
                'email'=>'wank@gacker.com',
                'active' => 1,
                ]
            ]
         );
        echo "valid? ";
        var_dump($form->isValid());
        echo "<br>",$entity->getEmail(), " is the entity's email...";

        $this->objectManager->persist($entity);

        try {

           //$this->objectManager->flush();            
        } catch (\Exception  $e) {
            echo "<br>".$e->getMessage();
        }
        $something = $form->getObject();

        echo get_class($something) . " comes from \$form->getObject()...";
        return false;

    }

    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        echo "it works";
        if ($this->events) {
            echo " and this->events is a ".get_class($this->events);
            if ($sharedManager =  $this->events->getSharedManager()) {
                echo " ... and we have a shared manager! ... ";
            } else {
                echo " but no shared event manager..." ;
            }
        }
        return false;
        return new ViewModel();
    }
    /**
     * temporary action for experimenting and doodling.
     *
     * this demonstrates a way to trigger an event. the listener was attached
     * by the factory at instantiation.
     */
    public function testAction()
    {
       echo "testAction works; ";echo "<br>note: i am ".self::class."<br>";
       //$this->events->trigger("doShit",$this,["message" => "this is the message parameter"]) ;
       $this->events->trigger(__FUNCTION__, $this,
               ["message" => "this is the message parameter"]
        ) ;

       return false;
    }

    /**
     * temporary; for doodling and experimenting.
     *
     * @return ViewModel
     */
    public function otherTestAction()
    {
        //return $viewModel;
    }
}

