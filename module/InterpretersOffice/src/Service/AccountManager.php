<?php /** module/InterpretersOffice/src/Service/AccountManager.php */

namespace InterpretersOffice\Service;

use Zend\Mail;
use Zend\View\ViewModel;
use Zend\View\Renderer\RendererInterface as ViewRendererInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

use Zend\Log\LoggerAwareInterface;
use Zend\Log\LoggerAwareTrait;
use Zend\EventManager\EventInterface;

use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Entity;

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;


/**
 * manages user account service
 */
class AccountManager implements LoggerAwareInterface
{

    use EventManagerAwareTrait, LoggerAwareTrait;

    /**
     * name of event for new account submission
     * @var string
     */
    const REGISTRATION_SUBMITTED = 'registrationSubmitted';

    /**
     * objectManager instance.
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * configuration data
     *
     * @var Array
     */
    private $config;

    /**
     * constructor
     */
    public function __construct(ObjectManager $objectManager, Array $config)
    {
        $this->objectManager = $objectManager;
        $this->config = $config;
    }

    /**
     * handles user account registration
     *
     * @param  Event  $event
     * @return void
     */
    public function onRegistrationSubmitted(EventInterface $event)
    {
        $log = $this->getLogger();
        /** @var Entity\User $user */
        $user = $event->getParam('user');
        $log->info("new user registration has been submitted for: "
            .$user->getUsername());

        $controller = $event->getTarget();
        $controller->layout()->setTemplate(
            'interpreters-office/email/layout.tidy.phtml'
        );
        $view = new \dViewModelViewModel();
        $view->content = "This here shit is your content";
        $opts = new $this->config['transport_options']['class'](
            $this->config['transport_options']['options']);
        $transport = new $this->config['transport']($opts);
        /*
        $text = new MimePart("Here is your plain text email.");
        $text->type = Mime::TYPE_TEXT;
        $text->charset = 'utf-8';
        $text->encoding = Mime::ENCODING_QUOTEDPRINTABLE;
        $htmlMarkup =  file_get_contents(
            'module/InterpretersOffice/view/interpreters-office/email/layout.tidy.phtml');
        $html = new MimePart($htmlMarkup);
        $html->type = Mime::TYPE_HTML;
        $html->charset = 'utf-8';
        $html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

        $body = new MimeMessage();
        $body->setParts([$text, $html]);
        $message = new Message();
        $message->setBody($body);
        $contentTypeHeader = $message->getHeaders()->get('Content-Type');
        $contentTypeHeader->setType('multipart/alternative');

        $transport->send($message);
        */
    }

    /**
     * gets config
     *
     * @return Array
     */
    public function getConfig()
    {
        return $this->config;
    }
}
