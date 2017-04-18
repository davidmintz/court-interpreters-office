<?php

/** module/Admin/src/Controller/PeopleController */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManagerInterface;
use InterpretersOffice\Admin\Form\InterpreterForm;
use InterpretersOffice\Entity;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 * controller for admin/interpreters.
 */
class InterpretersController extends AbstractActionController
{
    /**
     * entity manager.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {

        return new ViewModel(['title' => 'interpreters']);
    }
    /**
     * finds interpreters
     * 
     * gets interpreters based on search criteria
     * 
     * @return boolean|ViewModel
     */
    public function findAction()
    {
        echo "shit is running!" ;
        return false;
    }


    /**
     * adds an Interpreter entity to the database.
     */
    public function addAction()
    {
        $viewModel = (new ViewModel())
                ->setTemplate('interpreters-office/admin/interpreters/form.phtml')
                ->setVariables(['title' => 'add an interpreter']);

        $form = new InterpreterForm($this->entityManager, ['action' => 'create']);
        $viewModel->form = $form;

        $request = $this->getRequest();
        $entity = new Entity\Interpreter();
        $form->bind($entity);
        if ($request->isPost()) {
            $form->setData($request->getPost());

            $repository = $this->entityManager->getRepository('InterpretersOffice\Entity\Language');
            // manually hydrate because we could not make that other shit work
            $data = $request->getPost()['interpreter']['interpreter-languages'];
            if (is_array($data))    {
                foreach ($data as $language) {
                    $id = $language['language_id'];
                    $certification = is_numeric($language['federalCertification']) ?
                        (bool) $data['federalCertification'] : null;
                    // or get them all in one shot with a DQL query?
                    $languageEntity = $repository->find($id);
                    $il = (new Entity\InterpreterLanguage($entity, $languageEntity))
                        ->setFederalCertification($certification);
                    $entity->addInterpreterLanguage($il);
                }
            }
            
            if (! $form->isValid()) {
                return $viewModel;
            }
            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            $this->flashMessenger()->addSuccessMessage(
                sprintf(
                    'The interpreter <strong>%s %s</strong> has been added to the database',
                    $entity->getFirstname(),
                    $entity->getLastname()
                )
            );
            //echo "success. NOT redirecting. <a href=\"/admin/interpreters/add\">again</a>";
            $this->redirect()->toRoute('interpreters');
        }

        return $viewModel;
    }

    /**
     * updates an Interpreter entity.
     */
    public function editAction()
    {
        $viewModel = (new ViewModel())
                ->setTemplate('interpreters-office/admin/interpreters/form.phtml')
                ->setVariable('title', 'edit an interpreter');
        $id = $this->params()->fromRoute('id');
        $entity = $this->entityManager->find('InterpretersOffice\Entity\Interpreter', $id);
        if (! $entity) {
            return $viewModel->setVariables(['errorMessage' => "interpreter with id $id not found"]);
        }
        
        $form = new InterpreterForm($this->entityManager, ['action' => 'update']);
        $form->bind($entity);
        $viewModel->setVariables(['form' => $form, 'id' => $id]);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            //echo '<pre>';print_r($request->getPost()['interpreter']['interpreter-languages']);echo '</pre>';
            $this->updateInterpreterLanguages(
                $entity,
                $request->getPost()['interpreter']['interpreter-languages']
            );
            if (! $form->isValid()) {
                return $viewModel;
            }
            $this->entityManager->flush();
            $this->flashMessenger()->addSuccessMessage(sprintf(
                'The interpreter <strong>%s %s</strong> has been updated.',
                $entity->getFirstname(),
                $entity->getLastname()
            ));
            $this->redirect()->toRoute('interpreters');
           // echo "success. NOT redirecting...<a href=\"/admin/interpreters/edit/$id\">again</a> ";
           // echo "<pre>"; var_dump($_POST['interpreter']['interpreter-languages']) ;
           //print_r($form->getMessages()); echo "</pre>";
        }

        return $viewModel;
    }

    /**
     * manually updates the Interpreter entity's languages.
     *
     * since we were unable to get the Doctrine hydrator to work, for reasons
     * that remain obscure, we have to do it ourself.
     *
     * @param Entity\Interpreter $interpreter
     * @param mixed              $languages   language data POSTed to us
     */
    public function updateInterpreterLanguages(Entity\Interpreter $interpreter, $languages)
    {
        if (! is_array($languages)) {
            // return the interpreter entity in an invalid state (no languages)
            $interpreter->removeInterpreterLanguages($interpreter->getInterpreterLanguages());
            return;
        }
        $repository = $this->entityManager->getRepository('InterpretersOffice\Entity\Language');
        // get $before and $after into two like-structured arrays for comparison
        // i.e.: [ language_id => certification, ]
        $before = [];
        $interpreterLanguages = $interpreter->getInterpreterLanguages();
        //printf("DEBUG: we have %d interpreter-languages...",count($interpreterLanguages));
        foreach ($interpreterLanguages as $il) {
            $array = $il->toArray();
            $before[$array['language_id']] = $array['federalCertification'];
        }
        $after = [];       
        foreach ($languages as $l) {           
            $after[$l['language_id']] = $l['federalCertification'] >= 0 ?
                    (bool)$l['federalCertification'] : null;
        }
        // what has been added?
        $added = array_diff_key($after, $before);
        if (count($added)) {
            foreach ($added as $id => $cert) {
                // to do: snag all the languages in one shot instead?
                $language = $repository->find($id);
                $obj = new Entity\InterpreterLanguage($interpreter, $language);
                $cert = $after[$id];
                $obj->setFederalCertification($cert);
                $interpreter->addInterpreterLanguage($obj);
            }
        }
        // what has been removed?
        $removed = array_diff_key($before, $after);
        if (count($removed)) {
            foreach ($interpreterLanguages as $il) {
                if (key_exists($il->getLanguage()->getId(), $removed)) {
                    $interpreter->removeInterpreterLanguage($il);
                }
            }
        }
        // was any certification field modified?
        foreach ($interpreter->getInterpreterLanguages() as $il) {
            $language = $il->getLanguage();
            $id = $il->getLanguage()->getId();
            $cert = $il->getFederalCertification();
            $submitted_cert = $after[$id];
            if ($cert !== $submitted_cert) {
                $il->setFederalCertification($submitted_cert);
            }
        }
    }
}
 
