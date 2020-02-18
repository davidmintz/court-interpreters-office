<?php
/**
 * module/Admin/src/Controller/EmailController.php
 */

namespace InterpretersOffice\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\Http\Response;
//use Doctrine\ORM\EntityManagerInterface;
//use InterpretersOffice\Entity;
//use InterpretersOffice\Entity\Event;
//use Laminas\Session\Container as Session;
use InterpretersOffice\Admin\Service\EmailService;

/**
 * EmailController
 *
 */
class EmailController extends AbstractActionController
{

    /**
     * email service
     *
     * @var EmailService
     */
    private $emailService;

    /**
     * constructor
     *
     * @param EmailService $emailService
     */
    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * (placeholder) for entry point to administer email templates
     *
     * @return ViewModel
     */
    public function templatesAction()
    {
        return (new ViewModel)->setTemplate('email/templates');
    }

    /**
     * displays form for batch email
     * @return ViewModel
     */
    public function formAction()
    {
        return new ViewModel([
            'recipient_list_options' => $this->emailService::$recipient_list_options,
            'site_config' => $this->emailService->getConfig()['site'] ?? [],
        ]);
    }

    public function indexAction()
    {
        // $response = $this->getResponse();
        // $response->getHeaders()->addHeaders(['Content-type' => 'application/json']);
        // $response->setContent(json_encode(['status'=>'OK']));
        // //(new JsonModel())->setTerminal(true);
        // // echo $response->toString();
        // // return false;
        // return $response;


    }



    /**
     *
     * processes batch email
     */
    public function batchEmailAction()
    {
        $log = $this->getEvent()->getApplication()->getServiceManager()->get('log');
        $file = './data/progress.txt';
        if (! \file_exists($file)) {
            touch($file);
        } else {
            $contents = trim(file_get_contents($file));
            if ($contents && $contents != 'done') {
                $log->info("aborting batch email job",['progress_file_contents'=>$contents]);
                $this->getResponse()->setStatusCode(503);
                return new JsonModel(['status'=>'error',
                    'message'=> $this->emailService::ERROR_JOB_IN_PROGRESS]);
            }
            $fp = fopen($file,'w');
            \ftruncate($fp,0);
            fclose($fp);
        }
        $filter = $this->filter($this->getRequest()->getPost());
        if (! $filter->isValid()) {
            return new JsonModel(['validation_errors'=>$filter->getMessages()]);
        }
        $data = $filter->getValues();
        $service = $this->emailService;
        $recipients = $service->getRecipientList($data['recipient_list']);
        $total = count($recipients);
        header("content-type: application/json");
        echo json_encode(['status'=>'started','count'=>count($recipients)]);
        // this here is critical ...
        if (function_exists('fastcgi_finish_request')) {
            session_write_close();
            \fastcgi_finish_request();
            // ...otherwise it will NOT work
        } else {
            /* good question. */
        }

        $transport = $service->getMailTransport();
        $message = $service->createEmailMessage();
        $message->setSubject($data['subject']);
        $config = $service->getConfig()['mail'];
        $message->setFrom($config['from_address'],$config['from_entity']);
        $layout = $service->getLayout();
        $i = 0;
        foreach($recipients as $person) {
            // work in progress !
            $body = $message->getBody();
            $text_part = $body->getParts()[0];
            $name = "{$person['firstname']} {$person['lastname']}";
            $markup = $service->renderMarkdown($data['body']);
            if ($data['salutation'] == 'personalized') {
                $markup = "<p>Dear $name:</p>" . $markup;
            }
            $html = $service->render($layout,$markup);
            $body->setParts([$text_part,$service->createHtmlPart($html),]);
            $message->setBody($body)->setTo($person['email'],$name);
            $log->debug("sending mail to {$person['email']}");
            $transport->send($message);
            file_put_contents($file,"++$i of $total");
        }
        file_put_contents($file,"done");

        return new JsonModel(['total'=>$total,'current'=> $i]);
    }

    /**
     * returns progress data for batch-email
     *
     * @return JsonModel
     */
    public function progressAction()
    {
        $text = file_get_contents('./data/progress.txt');

        return new JsonModel(['status'=>$text,]);
    }

    private function filter(\Laminas\Stdlib\Parameters $data)
    {
        $filter = $this->emailService->getBatchEmailInputFilter();
        $filter->setData($data);

        return $filter;
    }
    /**
     * validates draft email
     * @return JsonModel
     */
    public function previewAction()
    {

        $filter = $this->filter($this->getRequest()->getPost());
        if (!$filter->isValid()) {
            $validation_errors = $filter->getMessages();
            return new JsonModel(['validation_errors'=>$validation_errors]);
        }
        return new JsonModel([
            'status'=>'OK',
            'markdown' => $this->emailService->renderMarkdown($filter->getValue('body')),
        ]);
    }

    /**
     *
     * Sends email regarding an Event or Request (entity).
     *
     * This action will usually invoked from /admin/schedule/view/<event_id>.
     *
     * @return JsonModel
     */
    public function emailEventAction()
    {
        if (! $this->getRequest()->isPost()) {
            /** @var \Laminas\Http\Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(405);
            return new JsonModel(['status' => 'error','message' => 'method not allowed']);
        }
        $csrf = $this->params()->fromPost('csrf', '');
        if (! (new \Laminas\Validator\Csrf('csrf', ['timeout' => 600]))->isValid($csrf)) {
            return new JsonModel(['status' => 'error','validation_errors' =>
                ['csrf' => 'security token is missing or expired']
            ]);
        }
        $data = $this->params()->fromPost('message');
        $result = $this->emailService->emailEvent($data);

        return new JsonModel($result);
    }
}

/* ---------- remind me to clean this up
// $path = $path = \realpath('./data/progress.sqlite');
// $db = new \PDO("sqlite:$path");
// $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
// $stmt = $db->prepare("UPDATE progress SET status = :status");
// $stmt->execute([':status' => "starting"]);
//$stmt->execute([':status' => "$i of 150"]);
//$stmt->execute([':status' => "done"]);

// $query = 'SELECT status FROM progress';
// $stmt = $db->query($query);

$factory = new \Laminas\InputFilter\Factory();
$inputFilter = $factory->createInputFilter([
    'to' => [
        'name' => 'to',
        'type'=> 'Laminas\InputFilter\CollectionInputFilter',
        'required' => true,
        'allow_empty' => false,
        'email' => [
            'name' => 'email',
            'required' => true,
            'allow_empty' => false,
            'validators' => [
                [
                    'name' => 'NotEmpty',
                    'options' => [
                        'messages' => [
                             'isEmpty' => 'email is required',
                         ],
                    ],
                    'break_chain_on_failure' => true,
                ],
                [
                    'name' => 'EmailAddress',
                    'options' => [
                        'messages' => [
                            \Laminas\Validator\EmailAddress::INVALID => 'email address is required',
                            \Laminas\Validator\EmailAddress::INVALID_FORMAT => 'invalid email address',
                        ],
                    ],
                    'break_chain_on_failure' => true,
                ],
            ],
        ],
        'name' => [
            'required' => false,
            'allow_empty' => false,
        ]
    ]

]);
$inputFilter->setData($data);
$valid = $inputFilter->isValid();
*/
