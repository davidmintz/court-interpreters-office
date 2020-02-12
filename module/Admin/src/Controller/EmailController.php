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
     * @return void
     */
    public function indexAction()
    {

    }

    /**
     * validates draft email
     * @return JsonModel
     */
    public function previewAction()
    {
        return new JsonModel(['status'=>'test one two, looking good']);
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
