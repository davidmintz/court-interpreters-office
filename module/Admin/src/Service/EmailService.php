<?php
/** module/InterpretersOffice/src/Service/EmailService.php */

declare(strict_types=1);

namespace InterpretersOffice\Admin\Service;

use InterpretersOffice\Service\EmailTrait;
use InterpretersOffice\Service\ObjectManagerAwareTrait;
use InterpretersOffice\Admin\Service\EmailService;
//use Doctrine\ORM\EntityManagerInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
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


/**
 * sends email from the admin/schedule interface
 */
class EmailService implements EventManagerAwareInterface, LoggerAwareInterface
{
    use EmailTrait;
    use LoggerAwareTrait;
    use EventManagerAwareTrait;

    /**
     * @var string
     */
    const CHANNEL = 'email';

    /**
     * configuration
     *
     * @var Array
     */
    private $config;

    /**
     * input filter for batch-email form
     *
     * @var Input\InputFilter
     */
    private $batchEmailInputFilter;

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
                        'name' => 'not_empty',
                        'options' => [
                            'isEmpty' => 'recipient list is required',
                        ],
                        'break_chain_on_failure' => true,
                    ],
                ],
            ],
        ]);

        return $this->batchEmailInputFilter;
    }


    /**
     * map email-subject hints to template filenames
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

    /**
     * constructor
     *
     * @param Array $config
     *
     */
    public function __construct(Array $config)
    {
        $this->config = $config;
        //$this->setObjectManager($em);
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
                return ! empty($address['name']) ? "{$a['name']} <{$a['email']}>"
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

        $layout = new ViewModel();
        $layout->setTemplate('interpreters-office/email/layout');

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
            // try {
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
}
