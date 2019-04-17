<?php
/**
 * module/Admin/src/Controller/EmailController.php
 */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Http\Response;
//use Doctrine\ORM\EntityManagerInterface;
//use InterpretersOffice\Entity;
//use InterpretersOffice\Entity\Event;
//use Zend\Session\Container as Session;
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
     *
     * Sends email regarding an Event (entity).
     *
     * This action will usually invoked from /admin/schedule/view/<event_id>.
     *
     * @return JsonModel
     */
    public function emailEventAction()
    {
        if (! $this->getRequest()->isPost()) {
            /** @var \Zend\Http\Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(405);
            return new JsonModel(['status'=>'error','message'=>'method not allowed']);
        }
        $csrf = $this->params()->fromPost('csrf','');
        if (! (new \Zend\Validator\Csrf('csrf',['timeout'=>600]))->isValid($csrf)) {
            return new JsonModel(['status'=>'error','validation_errors'=>
                ['csrf' => 'security token is missing or expired']
            ]);
        }
        $data = $this->params()->fromPost('message');
        $result = $this->emailService->emailEvent($data);

        return new JsonModel($result);
    }
}
/* ----------
$factory = new \Zend\InputFilter\Factory();
$inputFilter = $factory->createInputFilter([

    'to' => [
        'name' => 'to',
        'type'=> 'Zend\InputFilter\CollectionInputFilter',
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
                            \Zend\Validator\EmailAddress::INVALID => 'email address is required',
                            \Zend\Validator\EmailAddress::INVALID_FORMAT => 'invalid email address',
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
