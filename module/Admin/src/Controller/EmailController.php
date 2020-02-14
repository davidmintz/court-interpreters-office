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
     * experimental
     *
     * @return [type] [description]
     */
    public function batchEmailAction()
    {
        $log = $this->getEvent()->getApplication()->getServiceManager()->get('log');
        $path = $path = \realpath('./data/progress.sqlite');
        $db = new \PDO("sqlite:$path");
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $stmt = $db->prepare("UPDATE progress SET status = :status");
        $stmt->execute([':status' => "starting"]);
        header("content-type: application/json");
        echo json_encode(['status'=>'started']);
        // this here is critical ...
        session_write_close();
        fastcgi_finish_request();
        // otherwise it will NOT work
        for ($i = 0; $i <= 150; $i++) {
            usleep(250*1000);
            $stmt->execute([':status' => "$i of 150"]);
            // this also works
            file_put_contents('./data/progress.txt',"$i of 150");
        }
        $stmt->execute([':status' => "done"]);
        //return new JsonModel(['status'=>'OK']);
    }

    /**
     * experimental
     *
     * @return JsonModel
     */
    public function progressAction()
    {
        $path = \realpath('./data/progress.sqlite');
        $db = new \PDO("sqlite:$path");
        $query = 'SELECT status FROM progress';
        $stmt = $db->query($query);
        // and this is working as well..
        $text = file_get_contents('./data/progress.txt');
        return new JsonModel(['status'=>$stmt->fetchColumn(),'text'=>$text]);
    }

    /**
     * validates draft email
     * @return JsonModel
     */
    public function previewAction()
    {
        $data = $this->getRequest()->getPost();
        $filter = $this->emailService->getBatchEmailInputFilter();
        $filter->setData($data);
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
        // if (isset($result['status']) && 'error' == $result['status']) {
        //     $this->getResponse()->setStatusCode(500);
        // }

        return new JsonModel($result);
    }
}
/* ----------
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
