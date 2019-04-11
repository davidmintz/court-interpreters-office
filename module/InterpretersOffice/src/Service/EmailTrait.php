<?php
/** module/InterpretersOffice/src/Service/EmailTrait.php */

declare(strict_types=1);

namespace InterpretersOffice\Service;

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Mime;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\TransportInterface;

/**
 * for convenient re-use
 *
 */
trait EmailTrait
{

    /** @var TransportInterface transport */
    protected $transport;

    /**
     * returns email transport
     *
     * @return TransportInterface $transport
     */
    public function getMailTransport() : TransportInterface
    {
        if ($this->transport) {
            return $this->transport;
        }
        $config = $this->config['mail'];
        $opts = new $config['transport_options']['class'](
        $config['transport_options']['options']);
        $this->transport = new $config['transport']($opts);

        return $this->transport;
    }

    /**
     * creates an email message
     *
     * @param  string $markup HTML content for email message
     * @param  string $textContent plain-text content for email message
     * @return Message
     */
    public function createEmailMessage(string $markup, string $textContent='') : Message
    {
        $html = new MimePart($markup);
        $html->type = Mime::TYPE_HTML;
        $html->charset = 'utf-8';
        $html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;
        if (!$textContent) {
            $textContent = 'You will need to view this message in an email client that supports HTML.';
        }
        $text = new MimePart($textContent);
        $text->type = Mime::TYPE_TEXT;
        $text->charset = 'utf-8';
        $text->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

        $body = new MimeMessage();
        $body->setParts([$text, $html]);
        $message = new Message();
        $message->setBody($body)->setEncoding('UTF-8');
        $contentTypeHeader = $message->getHeaders()->get('Content-Type');
        $contentTypeHeader->setType('multipart/alternative');

        return $message;
    }
}
