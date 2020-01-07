<?php
/**
 *
 */

namespace InterpretersOffice\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
//use Laminas\View\Renderer\PhpRenderer;
//use Laminas\Http\Response;
use Doctrine\Common\Persistence\ObjectManager;
use InterpretersOffice\Entity;
use InterpretersOffice\Entity\Repository\CourtClosingRepository;



/**
 *  ExampleController.
 *
 *  just for dicking around and experimentation
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


    /**
     * dicking around
     *
     * @param  Array  $config
     * @return ViewModel
     */
    public function mailTestOne(Array $config)
    {

        $text = new MimePart("\nthis is your plain text part of the message\n");
        $text->type = \Laminas\Mime\Mime::TYPE_TEXT;
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
}
