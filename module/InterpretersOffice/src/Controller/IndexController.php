<?php
/**
 * module/InterpretersOffice/src/Controller/IndexController.php.
 */

namespace InterpretersOffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 *  IndexController.
 *
 *  Currently, just for making sure the application runs, basic routing is
 *  happening, service container is working, views are rendered, etc.
 */
class IndexController extends AbstractActionController
{
    /**
     * for informal testing/experimenting.
     *
     * @var \InterpretersOffice\Form\Factory\AnnotatedEntityFormFactory
     */
    protected $formFactory;

    /**
     * for informal testing/experimenting.
     *
     * @var Doctrine\ORM\EntityManagerInterface;
     */
    protected $em;

    /**
     * constructor arguments are temporary, for informal testing/experimenting.
     *
     * @param \InterpretersOffice\Form\Factory\AnnotatedEntityFormFactory $formFactory
     * @param EntityManagerInterface                                      $em
     */
    public function __construct($formFactory, $em)
    {
        $this->formFactory = $formFactory;
        $this->em = $em;
    }

    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $connection = $this->em->getConnection();
        $driver = $connection->getDriver()->getName();

        return new ViewModel(['driver' => $driver]);
    }
    /**
     * temporary action for experimenting and doodling.
     *
     * this demonstrates that we can build a form from annotations
     * and bind the form to a Doctrine entity, then add more elements
     */
    public function testAction()
    {
        $em = $this->em;
        // http://stackoverflow.com/questions/12002722/using-annotation-builder-in-extended-zend-form-class/18427685#18427685
        $builder = new  \Zend\Form\Annotation\AnnotationBuilder($em);

        //http://stackoverflow.com/questions/29335878/zend-framework-2-form-issues-using-doctrine-as-a-hydrator
        //  you should invoke setHydrator() on form itself after adding the base fieldset.

        $form = $builder->createForm(\InterpretersOffice\Entity\Person::class);
        $form->setHydrator(new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($em));
        // the firstname, middlename and lastname elements have already been
        // added and configured.
        // this demonstrates that we can add more after the fact
        $element = new \DoctrineModule\Form\Element\ObjectSelect('hat',
        [
            'object_manager' => $em,
            'target_class' => 'InterpretersOffice\Entity\Hat',
            'property' => 'name',
            'label' => 'hat',
            'display_empty_item' => true,
        ]);
        $filter = $form->getInputFilter();
        //\Zend\Debug\Debug::dump(get_class_methods($filter));
        $filter->add([
            'name' => 'hat',
            'validators' => [
                [
                    'name' => 'Zend\Validator\NotEmpty',
                    'options' => [
                        'messages' => [
                            'isEmpty' => 'the shit is empty, yo!',
                        ],
                    ],
                ],
            ],
        ]);
        //https://docs.zendframework.com/zend-inputfilter/intro/
        $form->add($element);

        $viewModel = new ViewModel(['form' => $form]);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $person = new \InterpretersOffice\Entity\Person();
            $form->bind($person);
            $form->setData($data);
            if (!$form->isValid()) {
                return $viewModel;
            }
            $em->persist($person);
            $em->flush();
            $this->flashMessenger()->addMessage('congratulations! you inserted an entity.');

            return $this->redirect()->toRoute('home');
        }

        return new ViewModel(['form' => $form]);
    }

    /**
     * temporary; for doodling and experimenting.
     *
     * @return ViewModel
     */
    public function otherTestAction()
    {
        return $viewModel;
    }
}
