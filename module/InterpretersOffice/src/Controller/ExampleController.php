<?php
/**
 * module/InterpretersOffice/src/Controller/ExampleController.php.
 */

namespace InterpretersOffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Form\PersonForm;
use InterpretersOffice\Entity;
use InterpretersOffice\Form\CreateBlogPostForm;

/**
 *  ExampleController.
 *
 *  Currently, just for making sure the application runs, basic routing is
 *  happening, service container is working, views are rendered, etc.
 */
class ExampleController extends AbstractActionController
{
    /**
     * objectManager instance.
     *
     * @var ObjectManager
     */
    protected $objectManager;

     
    public function bootstrap4Action()
    {
        $this->layout()->setTemplate('layout/bs-4.layout.phtml');
        $view = new ViewModel();
        $view->setTemplate('interpreters-office/example/bootstrap4.phtml');
        return $view;
        //return false;
    }
    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }
    /**
     * just for doodling with examples from Doctrine github site.
     *
     * @return array
     */
    public function createAction()
    {
        // Get your ObjectManager from the ServiceManager
        $objectManager = $this->objectManager;

        // Create the form and inject the ObjectManager
        $form = new CreateBlogPostForm($objectManager);

        // Create a new, empty entity and bind it to the form
        $blogPost = new Entity\BlogPost();
        $form->bind($blogPost);

        if ($this->request->isPost()) {
            $form->setData($this->request->getPost());

            if ($form->isValid()) {
                $objectManager->persist($blogPost);
                $objectManager->flush();
            }
        }

        return ['form' => $form];
    }

    /**
     * just for fool around with person form and fieldset. to be removed.
     *
     * @return bool
     */
    public function formAction()
    {
        echo 'shit works in formAction ... ';

        $form = new PersonForm($this->objectManager);

        $entity = new \InterpretersOffice\Entity\Person();

        $form->bind($entity);

        $form->setData([
            'person-fieldset' => [
                'firstname' => 'Wank',
                'lastname' => 'Gackersly',
                'email' => 'wank@gacker.com',
                'active' => 1,
                ],
            ]);
        echo 'valid? ';
        var_dump($form->isValid());
        echo '<br>',$entity->getEmail(), " is the entity's email...";

        $this->objectManager->persist($entity);

        try {
            //$this->objectManager->flush();
        } catch (\Exception  $e) {
            echo '<br>'.$e->getMessage();
        }
        $something = $form->getObject();

        echo get_class($something).' comes from $form->getObject()...';

        return false;
    }

    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $em = $this->objectManager;
       // 3 queries
        $entity = $em->find('InterpretersOffice\Entity\Judge', 11);
       // 0 queries
        $defaultLocation = $entity->getDefaultLocation();
       // 1 queries
        $parent_location = $defaultLocation->getParentLocation();
       //if ($parent_location) {}
        return false;
    }
    /**
     * temporary action for experimenting and doodling.
     *
     * this demonstrates a way to trigger an event. the listener was attached
     * by the factory at instantiation.
     */
    public function testAction()
    {
        echo 'testAction works; ';
        echo '<br>note: i am '.self::class.'<br>';
       //$this->events->trigger("doShit",$this,["message" => "this is the message parameter"]) ;
        $this->events->trigger(
            __FUNCTION__,
            $this,
            ['message' => 'this is the message parameter']
        );

        return false;
    }

    /**
     * temporary; for doodling and experimenting.
     *
     * @return ViewModel
     */
    public function otherTestAction()
    {
        $object = new \InterpretersOffice\Entity\Interpreter();
        $object->setLastname('Mintz');
        $em = $this->objectManager;
        $hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($em);
        //$hydrator->ex
        $data = [
           'lastname' => 'Mintz',
            'firstname' => 'David',
            'email' => 'david@example.com',
            'hat' => 1,
            'interpreterLanguages' => [
               ['language' => 62, 'interpreter' => $object],
            ],
        ];
        $interpreter = $hydrator->hydrate($data, $object);
        echo $interpreter->getLastName();

        return false;
    }
}
