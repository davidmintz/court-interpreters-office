<?php
/** module/InterpretersOffice/src/Service/EmailService.php */

declare(strict_types=1);

namespace InterpretersOffice\Admin\Service;


use InterpretersOffice\Service\EmailTrait;
use InterpretersOffice\Service\ObjectManagerAwareTrait;
use InterpretersOffice\Admin\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Zend\Validator\EmailAddress;
use Zend\View\Renderer\RendererInterface as Renderer;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationServiceInterface;

use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EmailService implements ObjectManagerAwareInterface, EventManagerAwareInterface
{
    use EmailTrait;
    use ObjectManagerAwareTrait;
    use EventManagerAwareTrait;

    /**
     * configuration
     *
     * @var Array
     */
    private $config;

    /**
     * entity manager
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

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
     * @var \Zend\Authentication\AuthenticationServiceInterface
     */
    private $auth;

    /**
     * constructor
     *
     * @param Array $config
     */
    function __construct(Array $config, EntityManagerInterface $em)
    {
        $this->config = $config;
        $this->setObjectManager($em);
    }

    /**
     * sends message
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
        $message = $this->createEmailMessage('');

        $message->setFrom($mail_config['from_address'],$mail_config['from_entity'])
            ->setBcc($mail_config['from_address'])
            ->setSubject($data['subject']);
        $message->getHeaders()->addHeaderLine('X-Sent-By', 'InterpretersOffice https://interpretersoffice.org');
        $log_comments = '';
        if (isset($data['cc'])) {
            $log_comments .= "Cc: ";
            foreach ($data['cc'] as $address) {
                $message->addCc($address['email'], $address['name'] ?: null );
            }
            $log_comments .= implode('; ',array_map(function($a){
                return $a['name'] ? "{$a['name']} <{$a['email']}>"
                    : $a['email'];
            },$data['cc']));
        }
        $result = ['sent_to' => [], 'cc_to' => []];
        $view = new ViewModel();
        /**  set template based on input etc */
        $template = $this->template_map[$data['template_hint']];
        $view->setTemplate("email/{$template}.phtml");

        $layout = new ViewModel();
        $layout->setTemplate('interpreters-office/email/layout');

        if (isset($data['event_details'])) {
            if (isset($data['event_details']['location'])) {
                $data['event_details']['location'] = str_replace('*','',$data['event_details']['location']);
            }
            $view->setVariables(['entity'=>$data['event_details'],'escaped'=>true]);
        }
        if (!empty($data['body'])) {
            $view->body = $data['body'];
            $view->setTemplate('email/blank-page');
        }
        $transport = $this->getMailTransport();
        $log_statement = $this->getStatement();

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
            file_put_contents("data/email-output.{$i}.html",$content);
            $message->setTo($address['email'], !empty($address['name']) ? $address['name'] : null );
            /** @var $pdo \PDO */
            $pdo = $this->getObjectManager()->getConnection();
            //if ($data['cc'])
            try {
                $pdo->beginTransaction();
                $params = [
                    ':timestamp' => date('Y-m-d H:i:s'),
                    ':recipient_id' => !empty($address['id']) ? $address['id'] : null,
                    ':email' => $address['email'],
                    ':subject'=> $data['subject'],
                    ':event_id' => $data['event_id'],
                    ':comments' => $log_comments,
                ];
                $log_statement->execute($params);
                $transport->send($message);
                $result['sent_to'][] = $address;
                $pdo->commit();

            } catch (\Throwable $e){
                $details = [
                    'status' => 'error',
                    'exception_class' => get_class($e),
                    'address' => $address['email'],
                    'name'   =>$address['name'],
                    'message' => $e->getMessage(),
                ];
                $this->getEventManager()
                    ->trigger('error',$this,['exception' => $e, 'details' => $details]);
                return array_merge($result, $details);
            }
        }
        if (!empty($data['cc'])) {
            $result['cc_to'] = $data['cc']; // for confirmation
        }
        return array_merge($result,['status'=>'success','info'=>"template: $template",]);
    }

    public function getStatement()
    {
        $user_id = $this->auth->getIdentity()->id;
        /** @var $pdo \PDO */
        $pdo = $this->getObjectManager()->getConnection();
        $sql = "INSERT INTO event_emails (event_id, `timestamp`, user_id, recipient_id, email, subject, comments)
            VALUES (:event_id, :timestamp, $user_id, :recipient_id, :email, :subject, :comments)";

        return $pdo->prepare($sql);

    }

    /**
     * Validates and filters data for composing message.
     *
     * This is crude, but using Zend\InputFilter\etc for this was too
     * complicated and we don't want or need a Zend\Form\Form.
     *
     * @param  Array $data
     * @return Array
     */
    public function validate(Array &$data) : Array
    {
        $validation_errors = ['to' => [], 'cc' => []];
        $alpha = $whitespace = null;
        $validator = new EmailAddress();

        if (! isset($data['to'])) {
            $validation_errors['to'][] = 'at least one "To" address is required';
        } elseif (! is_array($data['to'])) {
            $validation_errors['to'][] = 'invalid parameter in "To" field';
        } elseif (!count($data['to'])) {
            $validation_errors['to'][] = 'at least one "To" address is required';
        } else {
            $validator = new EmailAddress();
            $alpha = new \Zend\I18n\Filter\Alpha(true);
            $whitespace = new \Zend\Filter\PregReplace(
                ['pattern' =>  '/\s+/', 'replacement' => ' ' ]);
            foreach ($data['to'] as $i => $address) {
                if (empty($address['email'])) {
                    $validation_errors['to'][] = 'missing email address in "To" recipient';
                } elseif (!$validator->isValid($address['email'])){
                    $validation_errors['to'][] = 'invalid email address: '.$address['email'];
                }
                if (!empty($address['name'])) {
                    $filtered = $whitespace->filter($alpha->filter($address['name']));
                    $data['to'][$i]['name'] = $filtered;
                }
            }
        }
        if (!$alpha) {
            $alpha = new \Zend\I18n\Filter\Alpha(true);
            $whitespace = new \Zend\Filter\PregReplace(
                ['pattern' =>  '/\s+/', 'replacement' => ' ' ]);
        }
        if (isset($data['cc'])) {
            if (! is_array($data['cc'])) {
                $validation_errors['cc'][] = 'invalid parameter in "Cc" field';
            } else {
                foreach ($data['cc'] as $i => $address) {
                    if (empty($address['email'])) {
                        $validation_errors['cc'][] = 'missing email address in "Cc" recipient';
                    } elseif (!$validator->isValid($address['email'])){
                        $validation_errors['cc'][] = 'invalid email address: '.$address['email'];
                    }
                    if (!empty($address['name'])) {
                        $filtered = $whitespace->filter($alpha->filter($address['name']));
                        $data['cc'][$i]['name'] = $filtered;
                    }
                }
            }
        }
        $data['subject'] = trim($whitespace->filter($data['subject']));

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
        /*
        if (empty($data['event_details']) and empty($data['body'])) {
            //$validation_errors['body'] = 'Either a message text or event details is required';
        }
        if (empty($data['template_hint']) &&  empty($data['body'])) {
            $validation_errors['body'] = "If you're not using boilerplate, some message text is required";
        }

        if ($data['template_hint'] == "your request"  && empty($data['body'])) {
            $validation_errors['body'] = "If you're contacting the submitter about this request, some message text is required";
        }
        /*
        * if event-details ARE included, template is REQUIRED.
        * @todo support event-details and WITHOUT template?
        /
        if (isset($data['event_details'])) {
            if (empty($data['template_hint']) && $data['template_hint'] != 'your request') {
                $validation_errors['template'] = "If event details are included, a boilerplate text is required.";
            } else {
                if (isset($data['template_hint']) && ! in_array($data['template_hint'], array_keys($this->template_map))) {
                    $validation_errors['template'] = "Invalid boilerplate text.";
                }
            }
        }
        */
        foreach (['to','cc'] as $field) {
            if (! count($validation_errors[$field])) {
                unset($validation_errors[$field]);
            }
        }

        $valid = count($validation_errors) ? false : true;

        return compact('valid','validation_errors');
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
