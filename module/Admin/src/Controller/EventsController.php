<?php
/**
 * module/Admin/src/Controller/LanguagesController.php.
 */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Doctrine\ORM\EntityManagerInterface;
use Zend\Authentication\AuthenticationServiceInterface;

use InterpretersOffice\Admin\Form;

use InterpretersOffice\Entity;

/**
 *  EventsController
 *
 */

/*
 SELECT e.id, e.date, e.time, t.name type, l.name language, 
 COALESCE(j.lastname, a.name) AS judge, p.name AS place, 
 COALESCE(s.lastname,as.name) submitter, submission_datetime FROM events e 
 JOIN event_types t ON e.eventType_id = t.id 
 JOIN languages l ON e.language_id = l.id 
 LEFT JOIN people j ON j.id = e.judge_id 
 LEFT JOIN anonymous_judges a ON a.id = e.anonymous_judge_id 
 LEFT JOIN people s ON e.submitter_id = s.id
 LEFT JOIN hats AS `as` ON e.anonymous_submitter_id = as.id
 LEFT JOIN locations p ON e.location_id = p.id;
 */
class EventsController extends AbstractActionController
{
    
    /**
     * entity manager
     * 
     * @var EntityManagerInterface
     */
    protected $entityManager;
    
    /**
     * authentication service
     * 
     * @var AuthenticationServiceInterface 
     */
    protected $auth;
    
    /**
     * constructor
     *
     * @param EntityManagerInterface $em
     * @param AuthenticationServiceInterface $auth
     */
    public function __construct(EntityManagerInterface $em, 
            AuthenticationServiceInterface $auth)
    {
        $this->entityManager = $em;
        $this->auth = $auth;
    }
    /**
     * index action
     *
     */
    public function indexAction()
    {
       
        return ['title' => 'schedule'];
    }

    /**
     * adds a new event
     */
    public function addAction()
    {
        
        
        $form = new Form\EventForm(
            $this->entityManager,
            [   'action' => 'create',
                'auth_user_role'=> $this->auth->getIdentity()->role,
                'object' => null,
            ]
        );

        $request = $this->getRequest();
        $form
             ->setAttribute('action', $request->getRequestUri());
        $event = new Entity\Event();
        
        $form->bind($event);
        // test

        $shit = $form->get("event");
        $shit->get("date")->setValue('10/27/2017');
        $shit->get("time")->setValue('10:00 am');
        $shit->get("judge")->setValue(948);
        $shit->get("language")->setValue(62);
        $shit->get("parent_location")->setValue(6);
        $shit->get("location")->setValue(11);
        $shit->get("eventType")->setValue(1);
        $shit->get("docket")->setValue("2016-CR-0345");
        $shit->get("anonymousSubmitter")->setValue("6");
        $shit->get("submission_date")->setValue("10/24/2017");
        $shit->get("submission_time")->setValue("10:17 am");
        $shit->get("submission_datetime")->setValue('2017-10-24 10:17:00');
        // end test
        $viewModel = (new ViewModel())
            ->setTemplate('interpreters-office/admin/events/form')
            ->setVariables([                
                'form'  => $form,
                ]);
        if ($request->isPost()) {
            //echo "<strong>POST['event']['defendantNames']</strong>";
            //var_dump($_POST['event']['defendantNames']);
            $input = $request->getPost();
            //printf('<pre>%s</pre>',print_r($input->get('event')['interpreterEvents'],true)); //return false;
            $this->preValidate($input,$form);
            //printf('<pre>%s</pre>',print_r($input->get('event')['interpreterEvents'],true)); //return false;
            //echo "<strong>\$input->get('event')['defendantNames']</strong>";
            //  var_dump($input->get('event')['defendantNames']);
            $form->setData($input);
            //printf('<pre>%s</pre>',print_r($input->get('event'),true)); 
           
            //return false;
            if (! $form->isValid()) {
                echo "validation failed ... ";
                var_dump($form->getMessages());
                /** @todo śanity-check 'event'
                */
                $event = $input->get('event');
                $defendantNames = empty($event['defendantNames']) ? [] : $event['defendantNames'];
                $interpreters = empty($event['interpreterEvents']) ? [] : $event['interpreterEvents'];
                return $viewModel->setVariables(compact('defendantNames','interpreters' ));

            } else {
              
                echo "validation OK... ";
                ///* // shit is not working here!
                $entity_collection = $event->getInterpreterEvents();
                $form_collection =  $form->get('event')->get('interpreterEvents');
                if ($entity_collection->count() == $form_collection->count()) {
                    echo "hydration shit is ON ITS WAY????<br>";                     
                }
                
                $this->entityManager->persist($event);
                $this->entityManager->flush();
                echo "YAY!!!!!!";
            }
            
        }
        return $viewModel;
    }
    /**
     * preprocesses incoming data
     * 
     * @param \Zend\Stdlib\Parameters $data
     * @param \InterpretersOffice\Admin\Form\EventForm $form
     */
    protected function preValidate(\Zend\Stdlib\Parameters $data, 
            Form\EventForm $form)
    {
        $event = $data->get('event');
        if (!$event['judge'] && empty($event['anonymousJudge'])) {
            $validator = new \Zend\Validator\NotEmpty([
                'messages' => ['isEmpty' => "judge is required"],
                'break_chain_on_failure' => true,
            ]);
            $judge_input = $form->getInputFilter()->get('event')->get('judge');
            $judge_input->setAllowEmpty(false);
            $judge_input->getValidatorChain()->attach($validator);
        }
        
        /** @todo untangle this and make error message specific to context */
        $anonSubmitterElement = $form->get('event')->get('anonymousSubmitter');
        $hat_options = $anonSubmitterElement->getValueOptions();
        $hat_id = $anonSubmitterElement->getValue();        
        $key = array_search($hat_id, array_column($hat_options, 'value'));
        $can_be_anonymous = (!$key) ? false : $hat_options[$key]['attributes']['data-can-be-anonymous'];
        //var_dump($hat_options[$key]['attributes']['data-can-be-anonymous']);
        if ((empty($event['submitter']) && empty($event['anonymousSubmitter'])) 
                or
            (!$can_be_anonymous  && empty($event['submitter']))
        ) {
            $validator = new \Zend\Validator\NotEmpty([
                'messages' => 
                    [ 'isEmpty' => 
                        "identity or description of submitter is required"],
                'break_chain_on_failure' => true,
            ]);
            $submitter_input = $form->getInputFilter()->get('event')->get('submitter');
            $submitter_input->setAllowEmpty(false);
            $submitter_input->getValidatorChain()->attach($validator);            
        }
        // end to-do ///////////////////////////////////////////////////////////
        
        // if NO submitter but YES anonymous submitter, submitter = NULL
        if (empty($event['submitter']) && !empty($event['anonymousSubmitter'])) {
            $event['submitter'] = null;
        // if YES submitter and YES anonymous submitter, anon submitter = NULL
        } elseif (!empty($event['submitter']) && !empty($event['anonymousSubmitter'])) {
            $event['anonymousSubmitter'] = null;
        }
        if (!empty($event['submission_date']) && !empty($event['submission_time'])) {            
            $event['submission_datetime'] = "$event[submission_date] $event[submission_time]";
        }
        if (isset($event['defendantNames'])) {
            $event['defendantNames'] = array_keys($event['defendantNames']);
        } 
        /** @todo the thing to do here is test datetime properties for changes, 
         and if there is no change, flat-out remove the element to stop Doctrine
         from insisting on updating anyway
         */
        //$form->get('event')->remove('date');
        $data->set('event',$event);
    }

    /**
     * edits an event
     *
     *
     */
    public function editAction()
    {
        $id = $this->params()->fromRoute('id');
        $event = $this->entityManager->find(Entity\Event::class,$id);
        if (! $event) {
             return (new ViewModel())
            ->setTemplate('interpreters-office/admin/events/form')
            ->setVariables([               
                'errorMessage'  => 
                "event with id $id was not found in the database."
             ]);
        }
        $form = new Form\EventForm(
            $this->entityManager,
            ['action' => 'update','object'=>$event,]
        );
        
        $request = $this->getRequest();
        $form->setAttribute('action', $request->getRequestUri());        
        $form->bind($event);
        //$e = $form->get('event')->get('eventType');
        //$renderer = $this->getEvent()->getApplication()->getServiceManager()->get("ViewRenderer");
        //$html = $renderer->formElement($e);
        //echo $renderer->escapeHtml($html);
        $defendantNames = [];
        if ($this->getRequest()->isPost()) {
            //var_dump($_POST['event']['defendantNames']);
            $data = $request->getPost();
            $this->preValidate($data,$form);
            $form->setData($data);
            if ($form->isValid()) {
                    //printf("<pre><%s</pre>",print_r($_POST['event']['defendantNames'],true));
                    
                //try { 
                    $this->entityManager->flush();
                    echo "yay! shit is valid and has been saved.";
                //} catch (\Exception $e) {
                  //  echo $e->getMessage();
                //}
                
            } else {
                echo "shit is NOT valid...";
                printf("<pre><%s</pre>",print_r($form->getMessages(),true));                 
                //$post = $this->params()->fromPost('event')['defendantNames'];
                //$defendantNames = $_POST['event']['defendantNames'];
                // printf("<pre><strong>POSTed deft names:</strong>%s</pre>",print_r($defendantNames,true));//print_r($_POST['event'],true)
            }
        }
        
        $viewModel = (new ViewModel())
            ->setTemplate('interpreters-office/admin/events/form')
            ->setVariables(compact('form','defendantNames'));

        return $viewModel;
    }
    
    /**
     * generates markup for an interpreter
     * 
     * @return Zend\Http\PhpEnvironment\Response
     * @throws \RuntimeException
     */
    public function interpreterTemplateAction()
    {
        $helper = new Form\View\Helper\InterpreterElementCollection();
        $factory = new \Zend\InputFilter\Factory();
        $inputFilter = $factory->createInputFilter(                
            $helper->getInputFilterSpecification()
        );
        $data = $this->params()->fromQuery();
        $inputFilter->setData($data);
        if (! $inputFilter->isValid()) {
            throw new \RuntimeException(
                "bad input parameters: "
                    .json_encode($inputFilter->getMessages(),\JSON_PRETTY_PRINT)
            );
        }
        $data['created_by'] =  $this->auth->getStorage()->read()->id;
        $html = $helper->fromArray($data);
        return $this->getResponse()->setContent($html);
    }
    
    
    /**
     * gets interpreter options for populating select
     * 
     * @return JsonModel
     */
    public function interpreterOptionsAction()
    {
        /** @var  \InterpretersOffice\Entity\Repository\InterpreterRepository $repository */
        $repository = $this->entityManager->getRepository(Entity\Interpreter::class);        
        $language_id = $this->params()->fromQuery('language_id');
        if (! $language_id) {
            $result = ['error' => 'missing language id parameter'];
        } else {
            $result = $repository->getInterpreterOptionsForLanguage($language_id);
        }
        return new JsonModel($result);        
    }
}
