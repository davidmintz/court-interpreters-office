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


use Zend\View\Renderer\RendererInterface as Renderer;

/*
const USER_ACCOUNT_INACTIVE = 'user_account_inactive';
const USER_ACCOUNT_NOT_FOUND = 'user_account_not_found';
const ACTIVE_ACCOUNT_FOUND   = 'active_account_found';
*/


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
     * a random is_string
     *
     * @var string
     */
    private $random_string;

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
        $log->info(__CLASS__. " triggered by " .get_class($event->getTarget())
            .": new user registration has been submitted for: "
            .$user->getUsername());
        /** @var Entity\VerificationToken $token */
        $token = $this->createVerificationToken($user);
        $this->purge($token->getId());
        $this->objectManager->persist($token);
        /** maybe the stuff we need should be passed as Event params instead? */
        $controller = $event->getTarget();

        // assemble the URL for email verification
        $uri = $controller->getRequest()->getUri();
        $scheme = $uri->getScheme() ?: 'https';
        $host =  $uri->getHost() ?: 'office.localhost';
        $log->debug("for starters:  {$scheme}://{$host}");
        $path = $event->getTarget()->url()->fromRoute('account/verify-email',
            ['id' => $token->getId(),'token' => $this->random_string]
        );
        $url = "{$scheme}://{$host}/{$path}";
        $view = (new ViewModel([
            'url' => $url,
            'person'=>$user->getPerson()])
            )
            ->setTemplate('interpreters-office/email/user_registration');
        $layout = $controller->layout();
        $layout->setTemplate('interpreters-office/email/layout')
            ->setVariable('content', $this->viewRenderer->render($view));

        $html = new MimePart($this->viewRenderer->render($layout));
        // DEBUG:
        file_put_contents('data/email-output.html', $this->viewRenderer->render($layout));
        $html->type = Mime::TYPE_HTML;
        $html->charset = 'utf-8';
        $html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

        $text = new MimePart("You need a client that supports HTML email.");
        $text->type = Mime::TYPE_TEXT;
        $text->charset = 'utf-8';
        $text->encoding = Mime::ENCODING_QUOTEDPRINTABLE;


        $body = new MimeMessage();
        $body->setParts([$text, $html]);
        $message = new Message();
        $message->setBody($body);
        $contentTypeHeader = $message->getHeaders()->get('Content-Type');
        $contentTypeHeader->setType('multipart/alternative');

        $opts = new $this->config['transport_options']['class'](
            $this->config['transport_options']['options']);
        $transport = new $this->config['transport']($opts);
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
        //

    }

    /**
     * work in progress
     *
     * @param  string $hash
     * @return [type]       [description]
     */
    public function verify($hash)
    {
        /** @var  Doctrine\DBAL\Connection $db */
        $db = $this->objectManager->getConnection();
        $sql = 'SELECT u.* FROM users u JOIN people p ON u.person_id = p.id
            WHERE MD5(LOWER(p.email)) = ? ORDER BY p.id DESC LIMIT 1';
        $stmt = $db->executeQuery($sql,[$hash]);
        return $stmt->fetch();
    }

    public function getRandomString()
    {
        if (! $this->random_string) {
            $this->random_string =  bin2hex(openssl_random_pseudo_bytes(16));
        }
        return $this->random_string;
    }
    /**
     * creates a VerificationToken
     *
     * @return Entity\VerificationToken
     */
    public function createVerificationToken(Entity\User $user)
    {
        $token = new Entity\VerificationToken();
        $id = md5(strtolower($user->getPerson()->getEmail()));
        $random = $this->getRandomString();
        $hash = password_hash($random,PASSWORD_DEFAULT);
        $expiration = new \DateTime('+ 30 minutes');
        $token->setId($id)->setToken($hash)->setExpiration($expiration);

        return $token;

    }

    public function purge($id)
    {
        $DQL = 'DELETE InterpretersOffice\Entity\VerificationToken t
            WHERE t.expiration > CURRENT_TIMESTAMP() OR t.id = :id';
        $query = $this->objectManager->createQuery($DQL)
        ->setParameters(['id'=>$id,]);
        return $query->getResult();
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
