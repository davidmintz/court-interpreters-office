<?php
/**
 * module/Admin/src/Controller/EmailController.php
 */

namespace InterpretersOffice\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
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

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function sendAction()
    {
        if (! $this->getRequest()->isPost()) {
            /** @var \Zend\Http\Response $response */
            $response = $this->getResponse();
            $response->setStatusCode(405);
            return new JsonModel(['status'=>'error','message'=>'method not allowed']);
        }
        $csrf = $this->params()->fromPost('csrf','');
        if (! (new \Zend\Validator\Csrf('csrf',['timeout'=>600]))->isValid($csrf)) {
            return new JsonModel(['status'=>'error','message'=>'security token is missing or expired']);
        }
        $data = $this->params()->fromPost('message');

        // test
        //$data['to'][0]['name'] = "<this>\n(shit)  is *#$^annoying\n";

        $result = $this->emailService->sendMessage($data);
        //$shit = print_r($data,true);
        // $this->getEvent()->getApplication()->getServiceManager()->get('log')
        //    ->info($shit);

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
