<?php /** module/InterpretersOffice/src/Service/AccountManager.php */

namespace InterpretersOffice\Service;

use Zend\Mail;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\RendererInterface as ViewRendererInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

use Zend\Log\LoggerAwareInterface;
use Zend\Log\LoggerAwareTrait;
use Zend\EventManager\EventInterface;

use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Entity;
use InterpretersOffice\Service\AccountManager;

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;

use Zend\View\Renderer\PhpRenderer;
use Zend\View\Renderer\RendererInterface as Renderer;


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
     * view Renderer
     *
     * @var Renderer
     */
    private $viewRenderer;

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
        $log->info(get_class($event).": new user registration has been submitted for: "
            .$user->getUsername());

        $opts = new $this->config['transport_options']['class'](
            $this->config['transport_options']['options']);
        $transport = new $this->config['transport']($opts);
        $view = (new ViewModel(['person'=>$user->getPerson()]))
            ->setTemplate('interpreters-office/email/layout.tidy.phtml');

        $markup = $this->viewRenderer->render($view);

        $text = new MimePart("You need a client that supports HTML email.");
        $text->type = Mime::TYPE_TEXT;
        $text->charset = 'utf-8';
        $text->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

        $html = new MimePart($markup);
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

        //*/
        //$view->content = "This here shit is your text content";
        /*
        $child = (new ViewModel())
            ->setTemplate('interpreters-office/email/user_registration.phtml');
        $layout->addChild($child,'content');
        */
        //$child->content = "This is some content in the child view.";
        //$view = new ViewModel();
        //$view->setTemplate('interpreters-office/email/user_registration.phtml');
        //$layout->addChild($view);
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

    /**
     * sets viewRenderer
     *
     * @param Renderer $viewRenderer
     * @return AccountManager
     */
    public function setViewRenderer(Renderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer;

        return $this;
    }
}
