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
use Zend\Mail\Transport\TransportInterface;

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
    const ERROR_USER_TOKEN_NOT_FOUND = 'valid user verification token not found';

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
     * one of two possible purposes for calling verify()
     * @var string
     */
    const RESET_PASSWORD = 'reset_password';

    /**
     * one of two possible purposes for calling verify()
     *
     * @var string
     */
    const CONFIRM_EMAIL = 'confirm_email';

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
     * password input filter
     *
     * for the password-reset form
     *
     * @var InputFilterInterface;
     */
    private $passwordInputFilter;

    /**
     * input filter specification for CSRF
     *
     * @todo This is not peculiar to this class, but universal to the whole
     * application, so it should be moved someplace sensible. There is already
     * a Form/CsrfElementCreationTrait.php that is heavily used, but this CSRF
     * spec has arisen from cases where we use Zend\InputFilter\InputFilter
     * without any Zend\Form|Form
     *
     * @var array
     */
    private $csrf_spec = [
        'name' => 'csrf',
        'validators' => [
            [
                'name' => 'NotEmpty',
                'options' => [
                    'messages' => [
                        Validator\NotEmpty::IS_EMPTY => 'missing security token',
                    ],
                ],
                'break_chain_on_failure' => true,
            ],
            [
                'name' => 'Csrf',
                'options' => [
                    'messages' => [
                        Validator\Csrf::NOT_SAME =>
                            'Invalid or expired security token. '
                            . 'Please reload the page and try again.',
                    ],
                ],
            ],
        ],
        'filters' => [
            ['name'=>'StringTrim']
        ]
    ];

    /**
     * mail transport
     *
     * @var TransportInterface
     */
    private $transport;

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
     * @param Request $request
     * @param string $route
     * @return string
     */
    public function assembleVerificationUrl(Entity\VerificationToken $token,
        Request $request, $route)
    {
        $uri = $request->getUri();
        $scheme = $uri->getScheme() ?: 'https';
        $host =  $uri->getHost() ?: 'office.localhost';
        $path = $this->pluginManager->get('url')->fromRoute($route,
            ['id' => $token->getId(),'token' => $this->random_string]
        );
        $this->url = "{$scheme}://{$host}{$path}";

        return $this->url;
    }

    /**
     * gets password input filter for reset
     *
     * @param \Zend\Session\Container $session
     * @return InputFilterInterface
     */
    public function getPasswordInputFilter(\Zend\Session\Container $session)
    {
        if ($this->passwordInputFilter) {
            return $this->passwordInputFilter;
        }
        $factory = new Factory();
        $this->passwordInputFilter = $factory->createInputFilter([

            'password' => [
                'validators' =>[
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                             'messages' => [
                                 Validator\NotEmpty::IS_EMPTY => 'password is required',
                             ],
                         ],
                         'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => 'StringLength',
                        'options' => [
                            'min' => 8,
                            'max' => '150',
                            'messages' => [
                                'stringLengthTooLong' => 'password length exceeds maximum (%max% characters)',
                                'stringLengthTooShort' => 'password length must be a minimum of %min% characters',
                            ]
                        ]
                    ]
                ],
            ],
            'password-confirm' => [
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                             'messages' => [
                                 Validator\NotEmpty::IS_EMPTY => 'password confirmation is required',
                             ],
                         ],
                         'break_chain_on_failure' => true,
                    ],
                    [
                        'name' => 'Identical',
                        'options' => [
                            'token' => 'password',
                            'messages' => [
                                'notSame' => 'password confirmation field does not match'
                            ],
                        ],
                    ],
                ],
            ],
            'token' => [
                'name' => 'token',
                'validators' => [
                    [
                        'name' => 'NotEmpty',
                        'options' => [
                             'messages' => [
                                 Validator\NotEmpty::IS_EMPTY =>'missing authentication token',
                             ],
                         ],
                    ],
                    [
                        'name' => Validator\Callback::class,
                        'options' => [
                            'callback' => function($value) use ($session){
                                return $value == $session->token;
                            },
                            'messages' => [
                                'callbackValue'=> 'sorry, invalid/expired session security token',
                            ],
                        ],
                    ],
                ],
            ],
            $this->csrf_spec,
        ]);

        return $this->passwordInputFilter;
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
            ],
            $this->csrf_spec,

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
     * @return self
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

        $token = $this->createVerificationToken($user);
        $url = $this->assembleVerificationUrl($token,$request,'account/reset-password');
        $log->debug("created url: $url");
        $this->purge($token->getId());
        $this->objectManager->persist($token);
        $this->objectManager->flush();
        $view = (new ViewModel(['url' => $url,'person'=>$user->getPerson()]))
            ->setTemplate('interpreters-office/email/password_reset');
        $layout = new ViewModel();
        $layout->setTemplate('interpreters-office/email/layout')
            ->setVariable('content', $this->viewRenderer->render($view));
        $html = $this->viewRenderer->render($layout);
        // for DEBUGGING
        file_put_contents('data/email-password-reset.html', $this->viewRenderer->render($layout));
        // end DEBUGGING
        $person = $user->getPerson();
        $message = $this->createEmailMessage($html,
            'To read this message you need an email client that supports HTML');
        $message->setFrom($this->config['from_address'],$this->config['from_entity'])
            ->setTo($person->getEmail(),$person->getFullName())
            ->setSubject('Interpreters Office: reset your password');
        $this->getMailTransport()->send($message);

        return $this;
    }

    /**
     * registers a new user account
     *
     * @param  Entity\User $user
     * @param  Zend\Http\Request $request
     * @return self
     */
    public function register(Entity\User $user, $request)
    {
        $log = $this->getLogger();
        $user->setRole($this->getDefaultRole());
        $this->objectManager->persist($user);
        $this->objectManager->persist($user->getPerson());
        $token = $this->createVerificationToken($user);
        $this->purge($token->getId());
        $this->objectManager->persist($token);
        $url = $this->assembleVerificationUrl($token,$request,'account/verify-email');
        $view = (new ViewModel(['url' => $url,'person'=>$user->getPerson()]))
            ->setTemplate('interpreters-office/email/user_registration');
        $layout = new ViewModel();
        $layout->setTemplate('interpreters-office/email/layout')
            ->setVariable('content', $this->viewRenderer->render($view));
        $html = $this->viewRenderer->render($layout);
        // for DEBUGGING
        file_put_contents('data/email-confirm-account.html', $this->viewRenderer->render($layout));
        // end DEBUGGING

        $message = $this->createEmailMessage($html,"To read this message you need a client that supports HTML email.");
        $person = $user->getPerson();
        $message->setFrom($this->config['from_address'],$this->config['from_entity'])
            ->setTo($person->getEmail(),$person->getFullName())
            ->setSubject('Interpreters Office: email confirmation for new user account');
        $this->getMailTransport()->send($message);

        return $this;

    }

    /**
     * verifies a new user's email address
     *
     * @param  string $hashed_id a hash of the user's email address
     * @param string $token a random string
     * @return Array  in the form ['data'=> array|null, 'error'=> string|null]
     */
    public function verify($hashed_id, $token, $purpose)
    {
        if (! in_array($purpose,['reset_password','confirm_email'])) {
            throw new \InvalidArgumentException(
                'verify() method requires argument "purpose" to be a string ' .
                'that is either "reset_password" or "confirm_email"'
            );
        }

        $log = $this->getLogger();

        /** @var  Doctrine\DBAL\Connection $db */
        $db = $this->objectManager->getConnection();
        $sql = 'SELECT t.token, t.expiration, p.id AS person_id,
                u.username, u.id, u.last_login, u.active,
                p.lastname, p.firstname, p.email,
                r.name AS role
            FROM verification_tokens t
            JOIN people p ON MD5(LOWER(p.email)) = t.id
            JOIN users u ON p.id = u.person_id
            JOIN roles r ON r.id = u.role_id
            WHERE t.id = ?';
        $stmt = $db->executeQuery($sql,[$hashed_id]);
        $data = $stmt->fetch();

        if (! $data) {
            $log->info("user/token not found: query failed with hash $hashed_id");
            return ['error'=>self::ERROR_USER_TOKEN_NOT_FOUND,'data'=>null];
        }
        //$purged = $this->purge($token);
        //$log->info(__METHOD__. " purged $purged verification tokens");
        $valid = password_verify($token,$data['token']);
        if (! $valid) {
            $log->info('verification token failed password_verify() '
                . "for user {$data['email']}"
            );

            return ['error'=>self::ERROR_TOKEN_VALIDATION_FAILED,
                'data'=>$data];
        }
        /* maybe we should ensure that this never happens */
        if (self::CONFIRM_EMAIL == $purpose && $data['active']) {
            $log->info('email verification: account has already been activated '
            . "for user {$data['email']}, person id {$data['person_id']}"
            );
        }
        /* a scenario that should never happen. maybe we should throw
        an exception */
        if (self::CONFIRM_EMAIL == $purpose && 'submitter' !== $data['role']) {
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
            WHERE t.expiration < CURRENT_TIMESTAMP() OR t.id = :id';
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
     * returns email transport
     *
     * @return TransportInterface $transport
     */
    function getMailTransport()
    {
        if ($this->transport) {
            return $this->transport;
        }
        $opts = new $this->config['transport_options']['class'](
        $this->config['transport_options']['options']);
        $this->transport = new $this->config['transport']($opts);

        return $this->transport;

    }

    function resetPassword($user_id, $password)
    {
        $log = $this->getLogger();
        /** @var Entity\User $user */
        $user = $this->objectManager->find(Entity\User::class, $user_id);
        if (! $user) {
            $this->log->info(__METHOD__.": user with id $user_id not found");
            return false;
        }
        $user->setPassword($password);
        $this->objectManager->flush();

        return true;

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
