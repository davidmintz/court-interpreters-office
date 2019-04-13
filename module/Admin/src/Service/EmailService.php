<?php
/** module/InterpretersOffice/src/Service/EmailService.php */

declare(strict_types=1);

namespace InterpretersOffice\Admin\Service;


use InterpretersOffice\Service\EmailTrait;
use Doctrine\ORM\EntityManagerInterface;
use Zend\Validator\EmailAddress;
use Zend\View\Renderer\RendererInterface as Renderer;
use Zend\View\Model\ViewModel;

class EmailService
{
    use EmailTrait;

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

    //private $filter_spec = [];

    /**
     * viewRenderer
     *
     * @var Renderer
     */
    private $viewRenderer;

    /**
     * constructor
     *
     * @param Array $config
     */
    function __construct(Array $config, EntityManagerInterface $em)
    {
        $this->config = $config;
        $this->entityManager = $em;
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
        $message = $this->createEmailMessage('<p>boink!</p>');
        $message->setFrom($mail_config['from_address'],$mail_config['from_entity'])
            ->setSubject($data['subject']);

        if (isset($data['cc'])) {
            foreach ($data['cc'] as $address) {
                $message->addCc($address['email'], $address['name'] ?: null );
            }
        }

        $view = new ViewModel();
        /**  @todo set template based on input etc */
        $template = 'event-update-notice';
        $view->setTemplate("interpreters-office/email/{$template}.phtml");
        /** @todo bear in mind event-update-notice is directed at interpreter */

        $layout = new ViewModel();
        $layout->setTemplate('interpreters-office/email/layout');

        if (isset($data['event_details'])) {
            $view->setVariables(['entity'=>$data['event_details'],'escaped'=>true]);
            // for example...


        }
        $transport = $this->getMailTransport();

        foreach ($data['to'] as $i => $address) {
            $view->to = $address;
            $layout->setVariable('content', $this->viewRenderer->render($view));
            $output = $this->viewRenderer->render($layout);
            file_put_contents("data/email-output.{$i}.html",$output);

            $message->setTo($address['email'], $address['name'] ?: null );
            try {
                $transport->send($message);
            } catch (\Throwable $e){
                return [
                    'status' => 'error',
                    'exception' => get_class($e),
                    'address' => $address['email'],
                    'name'   =>$address['name'],
                    'message' => $e->getMessage(),
                ];
            }
        }


        /* // something like....
        $view = new ViewModel(compact('request','user_event',
            'updates','interpreters','user'));
        $view->setTemplate('interpreters-office/email/autocancellation-notice');
        $layout = new ViewModel();
        $layout->setTemplate('interpreters-office/email/layout')
            ->setVariable('content', $this->viewRenderer->render($view));
        $output = $this->viewRenderer->render($layout);
        // debug
        file_put_contents('data/email-autocancellation.html',$output);
        */

        return ['status'=>'success','ps'=>'only kidding', 'data'=>print_r($data,true)];
    }

    /**
     * validates and filters data for composing message
     *
     * @param  Array $data
     * @return Array
     */
    public function validate(Array &$data) : Array
    {

        $validation_errors = [];
        $alpha = $whitespace = null;
        $validator = new EmailAddress();

        if (! isset($data['to'])) {
            $validation_errors['to'] = 'at least one "To" address is required';
        } elseif (! is_array($data['to'])) {
            $validation_errors['to'] = 'invalid parameter in "To" field';
        } elseif (!count($data['to'])) {
            $validation_errors['to'] = 'at least one "To" address is required';
        } else {

            $validator = new EmailAddress();
            $alpha = new \Zend\I18n\Filter\Alpha(true);
            $whitespace = new \Zend\Filter\PregReplace(
                ['pattern' =>  '/\s+/', 'replacement' => ' ' ]);
            foreach ($data['to'] as $i => $address) {
                if (empty($address['email'])) {
                    $validation_errors['to'] = 'missing email address in "To" recipient';
                } elseif (!$validator->isValid($address['email'])){
                    $validation_errors['to'] = 'invalid email address: '.$address['email'];
                }
                if (isset($validation_errors['to'])) {
                    break;
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
                $validation_errors['cc'] = 'invalid parameter in "Cc" field';
            } else {
                foreach ($data['cc'] as $i => $address) {
                    if (empty($address['email'])) {
                        $validation_errors['cc'] = 'missing email address in "Cc" recipient';
                    } elseif (!$validator->isValid($address['email'])){
                        $validation_errors['cc'] = 'invalid email address: '.$address['email'];
                    }
                    if (isset($validation_errors['cc'])) {
                        break;
                    }
                    if (!empty($address['name'])) {
                        $filtered = $whitespace->filter($alpha->filter($address['name']));
                        $data['cc'][$i]['name'] = $filtered;
                    }
                }
            }
        }
        $data['subject'] = trim($whitespace->filter($data['subject']));
        if (empty($data['subject'])) {
            $validation_errors['subject'] = 'a valid subject line is required';
        }
        if (empty($data['event_details']) and empty($data['body'])) {
            $validation_errors['body'] = 'Either a message text or event details is required';
        }
        $valid = 0 == count($validation_errors);

        return compact('valid','validation_errors');
    }

    /**
     * sets viewRenderer
     *
     * @param Renderer $viewRenderer
     * @return EmailService
     */
    public function setViewRenderer(Renderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer;

        return $this;
    }


}
