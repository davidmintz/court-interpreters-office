<?php
/** module/InterpretersOffice/src/Service/EmailService.php */

declare(strict_types=1);

namespace InterpretersOffice\Admin\Service;

use InterpretersOffice\Service\EmailTrait;
use InterpretersOffice\Service\ObjectManagerAwareTrait;
use Laminas\Validator\EmailAddress;
use Laminas\View\Renderer\RendererInterface as Renderer;
use Laminas\View\Model\ViewModel;
use Laminas\Authentication\AuthenticationServiceInterface;
use InterpretersOffice\Entity;
use InterpretersOffice\Request\Entity\Request;
use Laminas\Mime\Mime;
use Laminas\Mime\Part as MimePart;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\Log\LoggerAwareTrait;
use Laminas\Log\LoggerAwareInterface;
use Laminas\InputFilter;

use Parsedown;

/**
 * sends email from the admin/schedule interface
 */
class EmailService implements EventManagerAwareInterface, LoggerAwareInterface
{
    use EmailTrait;
    use LoggerAwareTrait;
    use EventManagerAwareTrait;
    use ObjectManagerAwareTrait;

    /**
     * @var string
     */
    const CHANNEL = 'email';


    /**
     * error message for concurrent batch-email jobs
     *
     * @var string
     */
     const ERROR_JOB_IN_PROGRESS = <<<EOD
Another batch email job appears to be in progress, and running these jobs in parallel is not recommended.
Please try again in a couple of minutes.
EOD;
    /**
     * configuration
     *
     * @var Array
     */
    private $config;

    const ACTIVE_INTERPRETERS = 'all active interpreters';
    const ACTIVE_SPANISH_INTERPRETERS = 'all active Spanish interpreters';
    const ACTIVE_SUBMITTERS = 'all active request submitters';
    const AVAILABILITY_SOLICITATION_LIST = 'contract interpreters on your availability-solicitation list';
    const OFFICE_STAFF = 'all Interpreters Office staff';
    const TEST_GROUP = 'test recipients';

    public static $recipient_list_options = [
        self::ACTIVE_INTERPRETERS => self::ACTIVE_INTERPRETERS,
        self::ACTIVE_SPANISH_INTERPRETERS => self::ACTIVE_SPANISH_INTERPRETERS,
        self::ACTIVE_SUBMITTERS => self::ACTIVE_SUBMITTERS,
        self::AVAILABILITY_SOLICITATION_LIST => self::AVAILABILITY_SOLICITATION_LIST,
        self::OFFICE_STAFF => self::OFFICE_STAFF,
        self::TEST_GROUP => self::TEST_GROUP,
    ];

    /**
     * input filter for batch-email form
     *
     * @var Input\InputFilter
     */
    private $batchEmailInputFilter;

    /** @var ParseDown */
    private $parseDown;

    /**
     * constructor
     *
     * @param Array $config
     *
     */
    public function __construct(Array $config)
    {
        $this->config = $config;
        $env = getenv('environment');
        if ($env == 'production') {
            unset(self::$recipient_list_options[self::TEST_GROUP]);
        }
    }

    /**
     * renders $text as Markdown
     *
     * @param  string $text
     * @return string
     */
    public function renderMarkdown(string $text) : string
    {
        if (!$this->parseDown) {
            $this->parseDown = new Parsedown();
        }

        return $this->parseDown->text($text);
    }

    /**
     * gets email recipient list
     *
     * @param  string $list
     * @return Array
     */
    public function getRecipientList(string $list) : Array
    {
        $qb = $this->getObjectManager()->createQueryBuilder()
            ->select('p.id, p.lastname, p.firstname, p.email');

        switch ($list) {
            case self::ACTIVE_INTERPRETERS;
                $qb->from(Entity\Interpreter::class, 'p')
                    ->where('p.active = true');
            break;
            case self::ACTIVE_SPANISH_INTERPRETERS;
                $qb->from(Entity\Interpreter::class, 'p')
                    ->join('p.interpreterLanguages', 'il')->join('il.language','l')
                    ->where('l.name = :spanish')
                    ->andWhere('p.active = true')
                    ->setParameters([':spanish'=>'Spanish']);
            break;
            case self::AVAILABILITY_SOLICITATION_LIST:
                $qb->from(Entity\Interpreter::class, 'p')
                ->where('p.active = true')->andWhere('p.solicit_availability = true');
            break;
            case self::ACTIVE_SUBMITTERS:
                $qb->from(Entity\Person::class, 'p')
                    ->join(Entity\User::class, 'u','WITH','u.person = p')
                    ->join('u.role','r')
                    ->where('u.active = true')->andWhere('r.name = :role')
                    ->setParameters([':role'=>'submitter']);
            break;
            case self::OFFICE_STAFF:
                $qb->from(Entity\Person::class, 'p')->join(Entity\User::class, 'u','WITH','u.person = p')
                ->join('u.role','r')
                ->where('u.active = true')->andWhere('r.name IN (:role)')
                ->setParameters([':role'=>['administrator','manager']]);
            break;
            case self::TEST_GROUP:
                $data = [
                    ['id'=> 123,'lastname'=>'Mintz','firstname'=>'David','email'=>'mintz@vernontbludgeon.com'],
                    ['id'=> 124,'lastname'=>'Mintz','firstname'=>'David','email'=>'david_mintz@nysd.uscourts.gov'],
                ];
                return $data;
            default:
            throw new \RunTimeException("unknown email recipient list: $list");
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * gets batch-email input filter
     * @return Input\InputFilter
     */
    public function getBatchEmailInputFilter() : InputFilter\InputFilter
    {
        if ($this->batchEmailInputFilter) {
            return $this->getBatchEmailInputFilter();
        }
        $this->batchEmailInputFilter = (new InputFilter\Factory())
        ->createInputFilter([
            'recipient_list' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages'=>['isEmpty' => 'recipient list is required'],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                ],
            ],
            'subject' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages'=>['isEmpty' => 'subject is required'],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => 'StringLength',
                        'required' => true,
                        'options' => [
                            'max' => '80',
                            'min' => '8',
                            'messages' => [
                                'stringLengthTooShort' => 'subject should be a minimum %min% characters',
                                'stringLengthTooLong' => 'subject cannot exceed %max% characters',
                            ],
                        ],
                    ],
                ],
            ],
            'body' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages'=>['isEmpty' => 'message body is required'],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => 'StringLength',
                        'required' => true,
                        'options' => [
                            'max' => '8000',
                            'min' => '40',
                            'messages' => [
                                'stringLengthTooShort' => 'message should be a minimum %min% characters',
                                'stringLengthTooLong' => 'message cannot exceed %max% characters',
                            ],
                        ],
                    ],
                ],
            ],
            'salutation' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages'=>['isEmpty' => 'salutation option is required'],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => 'InArray',
                        'options' => [
                            'haystack' => ['personalized','other'],
                            'messages' => [
                                'notInArray' => 'invalid option for salutation',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        // to be continued
        return $this->batchEmailInputFilter;
    }


    /**
     * Maps email-subject options to template filenames
     *
     * The form for emailing someone in relation to a specific event provides
     * options for templates they can choose depending on the context. These
     * template files are in module/Admin/view/email/.
     *
     * @var array
     */
    private $template_map = [
        '' => 'blank-page',
        'your request' => 'blank-page',
        'available' => 'assignment-availability-notice',
        'confirmation' => 'assignment-confirmation-notice',
        'cancellation' => 'interpreter-cancellation-notice',
        'update' => 'event-update-notice',
    ];

    /**
     * viewRenderer
     *
     * @var Renderer
     */
    private $viewRenderer;

    /**
     * auth
     *
     * @var \Laminas\Authentication\AuthenticationServiceInterface
     */
    private $auth;

    public function getLayout() : ViewModel
    {
        return (new ViewModel())->setTemplate('interpreters-office/email/layout');
    }
    /**
     * gets configuration
     * @return array
     */
    public function getConfig() : Array
    {
        return $this->config;
    }

    /**
     * sends email message
     *
     * @param  Array $data
     * @return Array result
     */
    public function emailEvent(Array $data) : Array
    {
        $validation = $this->validate($data);
        if (! $validation['valid']) {
            return $validation;
        }
        $mail_config = $this->config['mail'];
        $message = $this->createEmailMessage();

        $message->setFrom($mail_config['from_address'], $mail_config['from_entity'])
            ->setBcc($mail_config['from_address'])
            ->setSubject($data['subject']);
        $log_comments = '';
        if (isset($data['cc'])) {
            $log_comments .= "Cc: ";
            foreach ($data['cc'] as $address) {
                $message->addCc($address['email'], ! empty($address['name']) ? $address['name'] : null);               
            }
            $log_comments .= implode('; ', array_map(function ($a) {
                return ! empty($a['name']) ? "{$a['name']} <{$a['email']}>"
                    : $a['email'];
            }, $data['cc']));
        }
        $result = ['sent_to' => [], 'cc_to' => []];
        $view = new ViewModel();
        /**  set template based on input etc */
        $template = $this->template_map[$data['template_hint']];
        // however...
        if (!isset($data['event_details'])) {
            $template = 'blank-page';
        }
        $view->setTemplate("email/{$template}.phtml");

        $layout = $this->getLayout();

        if (isset($data['event_details'])) {
            if (isset($data['event_details']['location'])) {
                $data['event_details']['location'] =
                    strip_tags(str_replace('*', '', $data['event_details']['location']));
            }
            $view->setVariables(['entity' => $data['event_details'],'escaped' => true]);
        }
        if (! empty($data['body'])) {
            $view->notes = $data['body'];
        }
        $transport = $this->getMailTransport();
        foreach ($data['to'] as $i => $address) {
            $view->to = $address;
            $layout->setVariable('content', $this->viewRenderer->render($view));
            $content = $this->viewRenderer->render($layout);
            $parts = $message->getBody()->getParts();
            $html = new MimePart($content);
            $html->type = Mime::TYPE_HTML;
            $html->charset = 'UTF-8';
            $html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;
            $message->getBody()->setParts([$parts[0],$html]);
            /* DEBUG */
            file_put_contents("data/email-output.{$i}.html", $content);
            $this->getLogger()->debug(__FUNCTION__.": using email template: '$template'");
            $message->setTo($address['email'], ! empty($address['name']) ? $address['name'] : null);
            $transport->send($message);
            $result['sent_to'][] = $address;
            $data['entity_id'] = isset($data['event_id']) ? $data['event_id']:$data['request_id'];
            if (isset($data['event_id'])) {
                $data['entity_id'] = $data['event_id'];
                $data['entity_class'] = Entity\Event::class;
            } else {
                $data['entity_id'] = $data['request_id'];
                $data['entity_class'] = Request::class;
            }
             $this->log([
                'recipient_id'=>! empty($address['id']) ? $address['id'] : null,
                'entity_id' => $data['entity_id'],
                'entity_class' => $data['entity_class'],
                'email' => $address,
                'subject' => $data['subject'],
                'comments' => $log_comments,
                'address' => $address,
            ]);
        }
        if (! empty($data['cc'])) {
            $result['cc_to'] = $data['cc']; // for confirmation
        }
        return array_merge($result, ['status' => 'success','info' => "template: $template",]);
    }

    public function render($layout,$markup = null) {
        if ($markup) {
            $layout->content = $markup;
        }
        return $this->viewRenderer->render($layout);
    }

    /**
     * sends list of interpreters
     *      
     * @param array $params
     */
    public function sendInterpreterList(Array $params) : array
    {
        $factory = new InputFilter\Factory();
        $input_filter = $factory->createInputFilter([
            'email' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages'=>['isEmpty' => 'email is required'],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => 'Callback',
                        'options' => [
                            'callBack' => function($value){
                                return (new EmailAddress())->isValid($value);
                            },
                            'messages' => [
                                'callbackValue' => '%value% does not appear to be a valid email address',
                            ],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                ],
            ],
            'recipient' => [
                'required' => false,
                'validators' => [
                    [
                        'name' => 'StringLength',
                        'required' => true,
                        'options' => [
                            'max' => '60',
                            'min' => '2',
                            'messages' => [
                                'stringLengthTooShort' => 'recipient name should be a minimum %min% characters',
                                'stringLengthTooLong' => 'recipient cannot exceed %max% characters',
                            ],
                        ],
                    ],
                ],

            ],
            'csrf' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages'=>['isEmpty' => 'required security token is missing'],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => 'Csrf',
                        'options' => [
                            'messages' => [
                                'notSame' => 'Invalid or expired security token. Please reload the page and try again.'
                            ],
                        ],
                    ]
                ],
            ],
            'language_id' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages'=>['isEmpty' => 'missing language_id parameter'],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                ],
                'filters' => [
                    [
                        'name' => 'Int'
                    ]
                ],
            ],
            'language' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages'=>['isEmpty' => 'language parameter'],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                ],                
            ],
            'active' => [
                'required' => true,
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                            'messages'=>['isEmpty' => 'missing "active" parameter'],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                ],
                'filters' => [
                    [
                        'name' => 'Int'
                    ]
                ],
            ],
        ]
        );
        $input_filter->setData($params);
        if ($input_filter->isValid()) {
            $input = $input_filter->getValues();
        } else {
            return [
                'status' => 'validation failed',
                'validation_errors' => $input_filter->getMessages(),
            ];
        }
        /** @var \InterpretersOffice\Entity\Repository\InterpreterRepository] $repository  */
        $repository = $this->objectManager->getRepository(Entity\Interpreter::class);
        $paginator = $repository->search($input);
        $total = $paginator->getTotalItemCount();
        $paginator->setItemCountPerPage($total);
        $view = new ViewModel();
        $view->setVariables(['paginator'=>$paginator,'language'=>$input['language']]);
        $view->setTemplate('email/interpreter-list.phtml');
        $layout = $this->getLayout();
        $layout->setVariable('content', $this->viewRenderer->render($view));
        $content = $this->viewRenderer->render($layout);
        $mail_config = $this->config['mail'];
        $message = $this->createEmailMessage();
        $message->setFrom($mail_config['from_address'], $mail_config['from_entity'])
            ->setBcc($mail_config['from_address'])
            ->setSubject("list of {$input['language']} interpreters")
            ->setTo($input['email'],$input['recipient']??null);
        $parts = $message->getBody()->getParts();
        $html = new MimePart($content);
        $html->type = Mime::TYPE_HTML;
        $html->charset = 'UTF-8';
        $html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;
        $message->getBody()->setParts([$parts[0],$html]);
        /* DEBUG */
        file_put_contents("data/email-list-output.html", $content);
        $this->getMailTransport()->send($message);
        
        return [
             'status' => "success",
             'data' => $input,
            //  'debug' => "total is $total, pages: ".$paginator->getPages()->pageCount
        ];
    }

    private function log(Array $data,string $channel = 'email')
    {

        $user = $this->auth->getIdentity()->username;
        $recipient = $data['address']['email'];
        if (isset($data['address']['name'])) {
            $recipient = $data['address']['name'] . " <{$recipient}>";
        }
        $message = sprintf(
            "user %s sent email to %s re: '%s'",
            $user,$recipient,$data['subject']
        );
        $this->getLogger()->info(
            $message,[
                'entity_class' => $data['entity_class'],
                'entity_id'    => $data['entity_id'],
                'channel'  => $channel,
                'recipient_id' => $data['recipient_id'],
                'comments' => $data['comments'],
                'recipient' => $recipient,
            ]
        );

        return $this;
    }

    /**
     * Validates and filters data for composing message.
     *
     * This is crude, but using Laminas\InputFilter\etc for this was too
     * complicated and we don't want or need a Laminas\Form\Form.
     * 
     * @todo we can do better.
     *
     * @param  Array $data
     * @return Array
     */
    public function validate(Array &$data) : Array
    {
        $validation_errors = ['to' => [], 'cc' => []];
        $alpha = $whitespace = null;
        $validator = new EmailAddress();
        $whitespace = new \Laminas\Filter\PregReplace(
                 ['pattern' =>  '/\s+/', 'replacement' => ' ' ]);
        if (! isset($data['to'])) {
            $validation_errors['to'][] = 'at least one "To" address is required';
        } elseif (! is_array($data['to'])) {
            $validation_errors['to'][] = 'invalid parameter in "To" field';
        } elseif (! count($data['to'])) {
            $validation_errors['to'][] = 'at least one "To" address is required';
        } else {
            foreach ($data['to'] as $i => $address) {
                if (empty($address['email'])) {
                    $validation_errors['to'][] = 'missing email address in "To" recipient';
                } elseif (! $validator->isValid($address['email'])) {
                    $validation_errors['to'][] = 'invalid email address: '.$address['email'];
                }
                if (! empty($address['name'])) {
                    $filtered = $whitespace->filter($address['name']);
                    $data['to'][$i]['name'] = $filtered;
                }
            }
        }
        $data['subject'] = trim($whitespace->filter($data['subject']));
        if (isset($data['cc'])) {
            if (! is_array($data['cc'])) {
                $validation_errors['cc'][] = 'invalid parameter in "Cc" field';
            } else {
                foreach ($data['cc'] as $i => $address) {
                    if (empty($address['email'])) {
                        $validation_errors['cc'][] = 'missing email address in "Cc" recipient';
                    } elseif (! $validator->isValid($address['email'])) {
                        $validation_errors['cc'][] = 'invalid email address: '.$address['email'];
                    }
                    if (! empty($address['name'])) {
                        $filtered = $whitespace->filter($address['name']);
                        $data['cc'][$i]['name'] = $filtered;
                    }
                }
            }
        }

        foreach (['template_hint','body'] as $field) {
            if (! empty($data[$field])) {
                $data[$field] = trim($data[$field]);
            } else {
                 $data[$field] = '';
            }
        }
        if (empty($data['subject'])) {
            $validation_errors['subject'] = 'a valid subject line is required';
        }
        // validation rules are kind of complicated here
        if ($data['template_hint'] == "your request"  && empty($data['body'])) {
            $validation_errors['body'] = "If you're contacting the submitter about this request, a message text is required";
        } else {
            if (isset($data['event_details'])) {
                if (empty($data['template_hint']) && empty($data['body'])) {
                    $validation_errors['body'] = 'If event details are included, either a boilerplate or message text is required.';
                }
            } else {
                if (empty($data['body'])) {
                    $validation_errors['body'] = 'If event details are not included, a message text is required.';
                }
            }
        }
        foreach (['to','cc'] as $field) {
            if (! count($validation_errors[$field])) {
                unset($validation_errors[$field]);
            }
        }

        $valid = count($validation_errors) ? false : true;

        return compact('valid', 'validation_errors');
    }

    /**
     * sets viewRenderer
     *
     * @param Renderer $viewRenderer
     * @return EmailService
     */
    public function setViewRenderer(Renderer $viewRenderer) : EmailService
    {
        $this->viewRenderer = $viewRenderer;

        return $this;
    }


    /**
     * sets auth
     *
     * @param  AuthenticationServiceInterface $auth [description
     * @return EmailService
     */
    public function setAuth(AuthenticationServiceInterface $auth) : EmailService
    {
        $this->auth = $auth;

        return $this;
    }

    /**
     * gets Auth instance
     *
     * @return AuthenticationServiceInterface|null
     */
    public function getAuth() : ? AuthenticationServiceInterface
    {
        return $this->auth;
    }
}
