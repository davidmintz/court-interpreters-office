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

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;

use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator;

use Zend\Http\PhpEnvironment\Request;

use Zend\View\Renderer\RendererInterface as Renderer;

use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Entity;
use InterpretersOffice\Service\AccountManager;


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
    const EVENT_REGISTRATION_SUBMITTED = 'registrationSubmitted';

    /**
     * name for successful verification event
     *
     * @var string
     */
    const EVENT_EMAIL_VERIFIED = 'email verified';

    /**
     * error code for failed email verification query
     *
     * For user registration, we follow the common practice of emailing a link
     * to the address the submitted by the user, which link has two url
     * parameters: one is a hash of the email address, the other a random string
     * that functions like a one-time password. This error means the query
     * failed, which can happen when an expired token is purged or if the query
     * parameters are wrong.
     *
     * @var string
     */
    const ERROR_USER_TOKEN_NOT_FOUND = 'user/token not found';

    /**
     * Code meaning invalid role for user self-registration
     *
     * We are currently operating on the theory that only users in the role
     * "submitter" are allowed to create their own user accounts. All the other
     * roles are privileged and have to be created manually by a user with
     * sufficient privileges.
     *
     * @var string
     */
    const ERROR_INVALID_ROLE_FOR_SELF_REGISTRATION =
        'invalid role for self-registration';

    /**
     * Error code for failed token validation.
     *
     * The verification token for confirming the email address is stored in the
     * database using password_hash(). This error means the submitted token
     * failed password_verify($token, $hashed_value).
     *
     * @var string
     */
    const ERROR_TOKEN_VALIDATION_FAILED = 'invalid url token';

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
     * a random string
     *
     * @var string
     */
    private $random_string;

    /**
     * email verification ur;
     * @var string
     */
    private $url;

    /**
     * controller plugin manager
     * @var \Zend\Mvc\Controller\PluginManager
     */
    private $pluginManager;

    /**
     * email input filter
     *
     * for the request-password-reset form
     *
     * @var InputFilterInterface;
     */
    private $emailInputFilter;

    /**
     * constructor
     */
    public function __construct(ObjectManager $objectManager, Array $config)
    {
        $this->objectManager = $objectManager;
        $this->config = $config;
    }

    /**
     * sets PluginManager
     *
     * @param \Zend\Mvc\Controller\PluginManager  $pluginManager
     */
    public function setPluginManager(\Zend\Mvc\Controller\PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }
    /**
     * Gets email verification url.
     *
     * This is to facilitate testing.
     *
     * @return string
     */
    public function getUrl()
    {
        if (! $this->url) {
            throw new \RuntimeException('assembleVerificationUrl() has to be '
            . ' called before '.__FUNCTION__. ' can be called'
            );
        }

        return $this->url;
    }
    /**
     * assembles the URL for email verification
     *
     * @param  Entity\VerificationToken $token
     * @return string
     */
    public function assembleVerificationUrl(Entity\VerificationToken $token, $request)
    {
        //$controller = $event->getTarget();
        $uri = $request->getUri();
        $scheme = $uri->getScheme() ?: 'https';
        $host =  $uri->getHost() ?: 'office.localhost';
        $path = $this->pluginManager->get('url')->fromRoute('account/verify-email',
            ['id' => $token->getId(),'token' => $this->random_string]
        );
        $this->url = "{$scheme}://{$host}{$path}";

        return $this->url;
    }

    /**
     * gets email input filter
     *
     * @return InputFilterInterface
     */
    public function getEmailInputFilter()
    {
        if ($this->emailInputFilter) {
            return $this->emailInputFilter;
        }
        $factory = new Factory();
        $this->emailInputFilter = $factory->createInputFilter([
            'email' => [
                'name' => 'email',
                'required' => true, 'allow_empty' => false,
                'validators' => [
                    [
                    'name' => 'NotEmpty',
                       'options' => [
                            'messages' => [
                                Validator\NotEmpty::IS_EMPTY => 'email is required',
                            ],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => Validator\EmailAddress::class,
                        'options' => [
                            'messages' => [
                                Validator\EmailAddress::INVALID => 'email address is required',
                                Validator\EmailAddress::INVALID_FORMAT => 'invalid email address',
                            ],
                        ],
                        'break_chain_on_failure' => true,
                    ],
                ],
                'filters' => [
                    ['name'=>'StringTrim']
                ]
            ]
        ]);
        return $this->emailInputFilter;

    }
    /**
     * gets the 'submitter' role
     * @return Entity\Role
     */
    protected function getDefaultRole()
    {
        return $this->objectManager->getRepository(Entity\Role::class)
            ->findOneBy(['name' => 'submitter']);
    }

    /**
     * handles a request to reset a password
     *
     * @param string $email user's email address
     * @param Request $request
     */
    public function requestPasswordReset($email, Request $request = null)
    {
        $dql = 'SELECT u FROM InterpretersOffice\Entity\User u JOIN u.person p
            WHERE p.email = :email';

        $user = $this->objectManager->createQuery($dql)
            ->setParameters(['email'=>$email])
            ->getOneOrNullResult();
        $log = $this->getLogger();
        $log->info(sprintf(
            "received password reset request for user %s, found %s, ip address %s",
            $email, $user ? $user->getPerson()->getFullName() : 'nobody',
            $request ? $request->getServer('REMOTE_ADDR','n/a') : 'none'
        ));
        if (! $user) {
            return false;
        }
        if (! $user->isActive()) {
            $log->info(
                sprintf(
                    'user active: no. last login: %s. returning false',
                    $user->getLastLogin() ?: 'never'
                ));
            return false;
        }
        $log->info("(not quite) sending email for password reset");

        return true;
    }

    /**
     * registers a new user account
     *
     * @param  Entity\User $user
     * @param  Zend\Http\Request $request
     * @return void
     */
    public function register(Entity\User $user, $request)
    {
        $log = $this->getLogger();
        $user->setRole($this->getDefaultRole());
        $this->objectManager->persist($user);
        $this->objectManager->persist($user->getPerson());
        $token = $this->createVerificationToken($user);
        $this->objectManager->persist($token);
        $url = $this->assembleVerificationUrl($token,$request);
        // $log->debug("token is: ".$this->random_string.", hash is "
        //     .$token->getToken());
        $view = (new ViewModel(['url' => $url,'person'=>$user->getPerson()]))
            ->setTemplate('interpreters-office/email/user_registration');
        $layout = new ViewModel();
        $layout->setTemplate('interpreters-office/email/layout')
            ->setVariable('content', $this->viewRenderer->render($view));
        $html = $this->viewRenderer->render($layout);
        // for DEBUGGING
        file_put_contents('data/email-output.html', $this->viewRenderer->render($layout));
        // end DEBUGGING

        $message = $this->createEmailMessage($html,"To read this message you need a client that supports HTML email.");

        $opts = new $this->config['transport_options']['class'](
            $this->config['transport_options']['options']);
        $transport = new $this->config['transport']($opts);
        $transport->send($message);

    }

    /**
     * verifies a new user's email address
     *
     * @param  string $hashed_id a hash of the user's email address
     * @param string $token a random string
     * @return Array  in the form ['data'=> array|null, 'error'=> string|null]
     */
    public function verify($hashed_id, $token)
    {
        /** @var  Doctrine\DBAL\Connection $db */
        $db = $this->objectManager->getConnection();
        // $sql = 'SELECT u.* FROM users u JOIN people p ON u.person_id = p.id
        //     WHERE MD5(LOWER(p.email)) = ? ORDER BY p.id DESC LIMIT 1';'wank_boinker@nysd.uscourts.gov'
        $sql = 'SELECT t.token, p.id AS person_id,
                u.username,
                u.id,
                u.last_login,
                u.active,
                p.lastname,
                p.firstname,
                p.email,
                r.name AS role
            FROM verification_tokens t
            JOIN people p ON MD5(LOWER(p.email)) = t.id
            JOIN users u ON p.id = u.person_id
            JOIN roles r ON r.id = u.role_id
            WHERE t.id = ?';
        $stmt = $db->executeQuery($sql,[$hashed_id]);
        $data = $stmt->fetch();
        $log = $this->getLogger();

        if (! $data) {
            $log->info("user/token not found: query failed with hash $hashed_id "
                ."and query: $sql");
            return ['error'=>self::ERROR_USER_TOKEN_NOT_FOUND,'data'=>null];
        }
        $valid = password_verify($token,$data['token']);
        if (! $valid) {
            $log->info('email verification token failed password_verify() '
                . "for (new?) user {$data['email']}"
            );

            return ['error'=>self::ERROR_TOKEN_VALIDATION_FAILED,
                'data'=>$data];
        }
        /* maybe we should ensure that this never happens */
        if ($data['active']) {
            $log->info('email verification: account has already been activated '
            . "for user {$data['email']}, person id {$data['person_id']}"
            );
        }
        /* a scenario that should never happen. maybe we should throw
        an exception */
        if ('submitter' !== $data['role']) {
            return [
                'error' => self::INVALID_ROLE_FOR_SELF_REGISTRATION,
                'data'=>null];
        }

        return ['data'=>$data,'error'=>null];
    }

    /**
     * Returns a random string.
     *
     * This creates the random string we use as a verification token.
     *
     * @return string
     */
    public function getRandomString()
    {
        if (! $this->random_string) {
            $this->random_string =  bin2hex(openssl_random_pseudo_bytes(16));
        }
        return $this->random_string;
    }
    /**
     * creates and returns a new VerificationToken entity
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
    /**
     * deletes all tokens that are expired or have id $id
     *
     * @param  string $id token id (a hash)
     * @return int  number of rows affected (?)
     */
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
     * creates an email message
     *
     * @todo make it a trait for convenient re-use?
     *
     * @param  string $html HTML
     * @param  string $text text
     * @return Message
     */
    public function createEmailMessage($markup,$textContent)
    {
        $html = new MimePart($markup);
        $html->type = Mime::TYPE_HTML;
        $html->charset = 'utf-8';
        $html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

        $text = new MimePart($textContent);
        $text->type = Mime::TYPE_TEXT;
        $text->charset = 'utf-8';
        $text->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

        $body = new MimeMessage();
        $body->setParts([$text, $html]);
        $message = new Message();
        $message->setBody($body);
        $contentTypeHeader = $message->getHeaders()->get('Content-Type');
        $contentTypeHeader->setType('multipart/alternative');

        return $message;


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
