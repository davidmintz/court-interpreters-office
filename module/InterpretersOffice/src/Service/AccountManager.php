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

use Doctrine\ORM\Query\ResultSetMappingBuilder;

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

        /** @var Entity\VerificationToken $token */
        $token = $this->createVerificationToken($user);
        $this->purge($token->getId());
        $this->objectManager->persist($token);

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
        // for argument's sake
        //  $result = $this->verify($token->getId());
        // printf("\nshit is a: %s\n",
        //      gettype($result)
        //  );
    }

    // not working... yet
    public function verify($hash)
    {
        printf("\nshit says: $hash\n");
        $db = $this->objectManager->getConnection();
        //  $db-> ...
        // http://doctrine-orm.readthedocs.org/projects/doctrine-orm/en/latest/reference/native-sql.html
        $rsm = new ResultSetMappingBuilder($this->objectManager);
        $rsm->addRootEntityFromClassMetadata(Entity\Person::class, 'people');
        // our inheritance scheme is not yet supported?
        //


        //$rsm->addJoinedEntityFromClassMetadata('MyProject\Address', 'a', 'u', 'address', array('id' => 'address_id'));
        //FROM users u INNER JOIN address a ON u.address_id = a.id";
        //$rsm->addJoinedEntityFromClassMetadata(Entity\Person::class,'p','u','person',['id'=>'person_id']);
        // there could be an additional, inactive, "historic" account with the same email. if so, assume
        // the more recently created is the one we're interested in. hence "ORDER BY... LIMIT"
        $sql = 'SELECT * FROM people WHERE md5(lower(email)) = :hash
         ORDER BY id DESC LIMIT 1';
        $query = $this->objectManager->createNativeQuery($sql,$rsm)->setParameters(['hash'=>$hash]);
        return $query->getOneorNullResult();





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
        $random = bin2hex(openssl_random_pseudo_bytes(16));
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
