<?php
/**
 * module/InterpretersOffice/src/Controller/ExampleController.php.
 */

namespace InterpretersOffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Response;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Form\PersonForm;
use InterpretersOffice\Entity;
use InterpretersOffice\Form\CreateBlogPostForm;

use SDNY\Vault\Service\Vault;
use Zend\Mail;

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mime\Mime;

/**
 *  ExampleController.
 *
 *  Currently, just for making sure the application runs, basic routing is
 *  happening, service container is working, views are rendered, etc.
 */
class ExampleController extends AbstractActionController
{
    /**
     * objectManager instance.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * constructor.
     *
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /*public function shitAction()
    {
        //return (new \Zend\Http\Response)->setContent("OK");
        $container = $this->getEvent()->getApplication()->getServiceManager();
        $config = $container->get('config')['vault'];
        printf('<pre>%s</pre>',print_r($config,true));
        ?><pre>readable $config['ssl_key']? <?php echo is_readable($config['ssl_key']) ?"yes":"no"?></pre><?php
        ?><pre>readable $config['ssl_cert']? <?php echo is_readable($config['ssl_cert']) ?"yes":"no"?></pre><?php
        //@var Vault  $vault
        $vault = $container->get('SDNY\Vault\Service\Vault');
        $shit = $vault->getEncryptionKey();
        echo $shit;

        return new ViewModel();
    }
    */


    /** just for doodling with examples from Doctrine github site.
     *
     * @return array
     */
    public function createAction()
    {
        // Get your ObjectManager from the ServiceManager
        $objectManager = $this->objectManager;

        // Create the form and inject the ObjectManager
        $form = new CreateBlogPostForm($objectManager);

        // Create a new, empty entity and bind it to the form
        $blogPost = new Entity\BlogPost();
        $form->bind($blogPost);

        if ($this->request->isPost()) {
            $form->setData($this->request->getPost());

            if ($form->isValid()) {
                $objectManager->persist($blogPost);
                $objectManager->flush();
            }
        }

        return ['form' => $form];
    }

    /**
     * just for fool around with person form and fieldset. to be removed.
     *
     * @return bool
     */
    public function formAction()
    {
        echo 'shit works in formAction ... ';

        $form = new PersonForm($this->objectManager);

        $entity = new \InterpretersOffice\Entity\Person();

        $form->bind($entity);

        $form->setData([
            'person-fieldset' => [
                'firstname' => 'Wank',
                'lastname' => 'Gackersly',
                'email' => 'wank@gacker.com',
                'active' => 1,
                ],
            ]);
        echo 'valid? ';
        var_dump($form->isValid());
        echo '<br>',$entity->getEmail(), " is the entity's email...";

        $this->objectManager->persist($entity);

        try {
            //$this->objectManager->flush();
        } catch (\Exception  $e) {
            echo '<br>'.$e->getMessage();
        }
        $something = $form->getObject();

        echo get_class($something).' comes from $form->getObject()...';

        return false;
    }
    public function mailTestOne($config)
    {

        $text = new MimePart("\nthis is your plain text part of the message\n");
        $text->type = \Zend\Mime\Mime::TYPE_TEXT;
        $text->charset = 'utf-8';
        $text->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

        $htmlContent =  file_get_contents('module/InterpretersOffice/view/interpreters-office/email/layout.tidy.phtml');
        $html = new MimePart($htmlContent);
        $html->type = Mime::TYPE_HTML;
        $html->charset = 'utf-8';
        $html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

        $body = new MimeMessage();
        $body->setParts([$html,$text]);

        $message = new Message();
        $message->setBody($body);

        $contentTypeHeader = $message->getHeaders()->get('Content-Type');
        $contentTypeHeader->setType('multipart/alternative');

        $message->setSubject("Here is your multipart/alternative message")
            ->setTo('david@davidmintz.org', 'david')
            ->setFrom("interpreters@nysd.uscourts.gov");

        $opts = new $config['transport_options']['class']( $config['transport_options']['options']);
        $transport = new $config['transport']($opts);
        $transport->send($message);
        $debug = "message was sent. FYI transport is a ".get_class($transport);

        return (new ViewModel(['debug'=>$debug]))
            ->setTemplate('interpreters-office/example/shit.phtml');
    }
    /**
     * index action.
     *
     * @return ViewModel
     */
    public function indexAction()
    {
       // 3 queries
        //$entity = $em->find('InterpretersOffice\Entity\Judge', 11);
       // 0 queries
        //$defaultLocation = $entity->getDefaultLocation();
       // 1 queries
        //$parent_location = $defaultLocation->getParentLocation();
       //if ($parent_location) {}
        $config = $this->getEvent()->getApplication()->getServiceManager()->get('config')['mail'];

        return $this->mailTestOne($config);
        //printf("<pre>%s</pre>",print_r($config,true));

        return (new ViewModel(['debug'=>"this is a test"]))
            ->setTemplate('interpreters-office/example/shit.phtml');
    }
    /**
     * temporary action for experimenting and doodling.
     *
     * this demonstrates a way to trigger an event. the listener was attached
     * by the factory at instantiation.
     */
    public function testAction()
    {
        echo 'testAction works; ';
        echo '<br>note: i am '.self::class.'<br>';
       //$this->events->trigger("doShit",$this,["message" => "this is the message parameter"]) ;
        $this->events->trigger(
            __FUNCTION__,
            $this,
            ['message' => 'this is the message parameter']
        );

        return false;
    }

    /**
     * temporary; for doodling and experimenting.
     *
     * @return ViewModel
     */
    public function otherTestAction()
    {
        $object = new \InterpretersOffice\Entity\Interpreter();
        $object->setLastname('Mintz');
        $em = $this->objectManager;
        $hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($em);
        //$hydrator->ex
        $data = [
           'lastname' => 'Mintz',
            'firstname' => 'David',
            'email' => 'david@example.com',
            'hat' => 1,
            'interpreterLanguages' => [
               ['language' => 62, 'interpreter' => $object],
            ],
        ];
        $interpreter = $hydrator->hydrate($data, $object);
        echo $interpreter->getLastName();

        return false;
    }
}
