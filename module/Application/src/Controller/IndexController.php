<?php
/**
 * module/Application/src/Controller/IndexController.php
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Interop\Container\ContainerInterface;

/**
 *  IndexController
 * 
 *  Currently, just for making sure the application runs, basic routing is 
 *  happening, service container is working, views are rendered, etc.
 */

class IndexController extends AbstractActionController
{
     /** 
      * service manager.
      * 
      * @var ContainerInterface 
      */
    protected $serviceManager;
    
    /**
     * constructor
     * 
     * @see Application\Controller\Factory\IndexControllerFactory
     * @param ContainerInterface $serviceManager
     */
    public function __construct(ContainerInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    /**
     * index action
     * 
     * @return ViewModel
     */
    public function indexAction()
    {
        $builder = new  \Zend\Form\Annotation\AnnotationBuilder($this->serviceManager->get('entity-manager'));
        $form    = $builder->createForm(\Application\Entity\Language::class);
        $em      = $this->serviceManager->get('entity-manager');
        $form->setHydrator(new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($em));
        return new ViewModel(['form'=>$form]);
    }

}
