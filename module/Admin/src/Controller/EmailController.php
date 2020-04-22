<?php
/**
 * module/Admin/src/Controller/EmailController.php
 */

namespace InterpretersOffice\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

use InterpretersOffice\Admin\Service\EmailService;
use InterpretersOffice\Admin\Service\BatchEmailService;
use Swift_Message;

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
     * 
     * @return ViewModel
     */
    public function formAction()
    {
        $sapi = php_sapi_name();
        if ($sapi !== 'fpm-fcgi') {
            $this->flashMessenger()->addErrorMessage(
                "Batch email is not currently supported under your server configuration. 
                The server API has to be <em>fpm-fcgi</em>; yours is <em>$sapi</em>."
            );
            $this->redirect()->toRoute('email');
        }
        return new ViewModel([
            'recipient_list_options' => $this->emailService::$recipient_list_options,
            'site_config' => $this->emailService->getConfig()['site'] ?? [],
        ]);
    }

    /**
     * index page for admin email
     * 
     * @return ViewModel
     */
    public function indexAction()
    {      
        return new ViewModel(['php_sapi_name'=>php_sapi_name()]);
    }

    /**
     * batch email
     *
     * a revision to get us away from Laminas\Mail which is
     * needlessly clumsy in our humble opinion
     */
    public function batchEmailAction()
    {
        if (!function_exists('fastcgi_finish_request')){
            throw new \RuntimeException(
                'batch email is currently not supported under server APIs other than fpm-fcgi');
        }
        $log = $this->getEvent()->getApplication()->getServiceManager()->get('log');
        $file = './data/progress.txt';
        if (! \file_exists($file)) {
            touch($file);
        } else {
            $contents = trim(file_get_contents($file));
            if ($contents && $contents != 'done') {
                $log->info("aborting batch email job", ['progress_file_contents' => $contents]);
                $this->getResponse()->setStatusCode(503);
                return new JsonModel(['status' => 'error',
                'message' => $this->emailService::ERROR_JOB_IN_PROGRESS]);
            }
            $fp = fopen($file, 'w');
            \ftruncate($fp, 0);
            fclose($fp);
        }
        $filter = $this->filter($this->getRequest()->getPost());
        if (! $filter->isValid()) {
            return new JsonModel(['validation_errors' => $filter->getMessages()]);
        }
        $data = $filter->getValues();

        $service = $this->emailService;
        $config = $service->getConfig()['mail'];
        $transport = (new BatchEmailService($config))->getTransport();
        /** @todo maybe move this to the BatchEmailService class */
        $recipients = $service->getRecipientList($data['recipient_list']);
        $total = count($recipients);
        header("content-type: application/json");
        echo json_encode(['status' => 'started','total' => count($recipients)]);
        // this here is critical ...
        session_write_close();
        \fastcgi_finish_request();
        // ...otherwise it will NOT work
        

        /** @var Swift_Message $message */
        $message = new Swift_Message();
        $message->setSubject($data['subject']);

        $message->setFrom([$config['from_address'] => $config['from_entity']]);
        $layout = $service->getLayout();
        $i = 0;
        foreach ($recipients as $person) {
            $markup = $service->renderMarkdown($data['body']);
            $name = "{$person['firstname']} {$person['lastname']}";
            if ($data['salutation'] == 'personalized') {
                $markup = "<p>Dear $name:</p>" . $markup;
            }
            $html = $service->render($layout, $markup);
            $message->setBody($html, 'text/html');
            $message->addPart($data['body'], 'text/plain')
            ->setTo([$person['email'] => $name]);
            $log->debug("sending mail re {$data['subject']} to {$person['email']}");
            $transport->send($message);
            file_put_contents($file, ++$i ." of $total");
        }
        file_put_contents($file, "done");
        $log->info("completed batch email to $total recipients re {$data['subject']}");
        return new JsonModel(['total' => $total,'current' => $i]);
    }

    
    public function progressAction()
    {
        $text = file_get_contents('./data/progress.txt');

        return new JsonModel(['status' => $text,]);
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
        if (! $filter->isValid()) {
            $validation_errors = $filter->getMessages();
            return new JsonModel(['validation_errors' => $validation_errors]);
        }
        return new JsonModel([
            'status' => 'OK',
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

    /**
     * sends a list of interpreters
     */
    public function sendInterpreterListAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $service = $this->emailService;
            $params = $this->params()->fromPost();
            $response = $service->sendInterpreterList($params);

            return new JsonModel($response);
        }

        return false;
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
