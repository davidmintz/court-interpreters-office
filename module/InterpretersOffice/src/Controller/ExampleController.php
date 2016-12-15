<?php
/**
 * module/InterpretersOffice/src/Controller/ExampleController.php.
 */

namespace InterpretersOffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 *  ExampleController.
 *
 *  Currently, just for making sure the application runs, basic routing is
 *  happening, service container is working, views are rendered, etc.
 */
class ExampleController extends AbstractActionController implements \Zend\EventManager\EventManagerAwareInterface
{
    
    use \Zend\EventManager\EventManagerAwareTrait;

    public function __construct()
    {
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
     * this demonstrates that we can build a form from annotations
     * and bind the form to a Doctrine entity, then add more elements
     */
    public function testAction()
    {
       echo "testAction works; ";echo "<br>note: i am ".self::class."<br>";

       $this->events->trigger("doShit",$this,["message" => "this is the message parameter"]) ;

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
