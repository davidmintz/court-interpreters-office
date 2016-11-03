<?php
/**
 * module/Application/src/Controller/LanguagesController.php
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Interop\Container\ContainerInterface;

use Application\Form\Factory\AnnotatedEntityFormFactory;
use Application\Entity\Language;

/**
 *  LanguagesController, for managing languages.
 * 
 * @todo get rid of ServiceManager dependency and become more specific about 
 * our dependencies.
 * @todo further, seriously consider a LanguageServiceInterface and LanguageService 
 * implementation that depends on the entity manager.
 *  
 */

class LanguagesController extends AbstractActionController
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
     * @see Application\Controller\Factory\SimpleEntityControllerFactory
     * @param ContainerInterface $serviceManager
     */
    public function __construct(ContainerInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        /** @todo make this an invokable we can get out of the container? */
        $this->factory = new AnnotatedEntityFormFactory();
        
    }
    /**
     * index action
     * 
     * @return ViewModel
     */
    public function indexAction()
    {
        
    }
    /**
     * updates a language
     * 
     * @return ViewModel
     */
    public function updateAction()
    {
       $id = $this->params()->fromRoute('id');
       if (! $id) {
           return $this->getFormViewModel(['errorMessage' => "invalid or missing id parameter"]);
       }
       $entity = $this->serviceManager->get('entity-manager')->find('Application\Entity\Language',$id);
       if (! $entity) {
           return $this->getFormViewModel(['errorMessage' => "language with id $id not found"]);
       }
       
       $manager = $this->serviceManager->get('FormElementManager');
       $form = $manager->build(Language::class,['object'=>$entity,'action'=>'update']);
       $form->bind($entity);
       $viewModel = $this->getFormViewModel(
              ['form'=>$form,'title'=>'edit a language','id'=>$id]
        );
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                return $viewModel;
            } 
            $em = $this->serviceManager->get('entity-manager');
            $em->flush();
            $this->flashMessenger()
                  ->addSuccessMessage("The language $language has been updated.");
            $this->redirect()->toRoute('languages');
        }
        return $viewModel;
    }
    
    /**
     * adds a new language
     * @return ViewModel
     */
    public function createAction()
    {
        
        $language = new Language();
        $manager = $this->serviceManager->get('FormElementManager');
        $form = $manager->build(Language::class,['object'=>$language,'action'=>'create']);
        $viewModel = $this->getFormViewModel(
                ['form'=>$form,'title'=>'add a language']);
        $form->bind($language);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (! $form->isValid()) {
                return $viewModel;
            } 
            try {
                $em = $this->serviceManager->get('entity-manager');
                $em->persist($language);
                $em->flush();
                $this->flashMessenger()
                      ->addSuccessMessage("The language $language has been added.");
                $this->redirect()->toRoute('languages');
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
        return $viewModel;
    }
    /**
     * get the viewModel
     * @param array $data
     * @return ViewModel
     */
    protected function getFormViewModel(Array $data) {
        return (new ViewModel($data))
                ->setTemplate('application/languages/form.phtml');
    }
}
