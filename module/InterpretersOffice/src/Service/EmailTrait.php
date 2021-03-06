<?php
/** module/InterpretersOffice/src/Service/EmailTrait.php */

declare(strict_types=1);

namespace InterpretersOffice\Service;

use Laminas\Mail\Message;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Mime;
use Laminas\Mime\Part as MimePart;
use Laminas\Mail\Transport\TransportInterface;
use Laminas\View\Model\ViewModel;
use InterpretersOffice\Entity;

/**
 * for convenient re-use
 *
 */
trait EmailTrait
{

    /** @var TransportInterface transport */
    protected $transport;

    /** @var ViewModel email layout */
    protected $layout;

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
        if ($config['transport_options']) {
            $opts = new $config['transport_options']['class'](
                $config['transport_options']['options']);
        } else {
            $opts = null;
        }
        $this->transport = new $config['transport']($opts);

        return $this->transport;
    }

    /**
     * creates HTML part
     *
     * @param string $markup
     * @return MimePart
     */
    public function createHtmlPart(string $markup)
    {
        $html = new MimePart($markup);
        $html->type = Mime::TYPE_HTML;
        $html->charset = 'UTF-8';
        $html->encoding = Mime::ENCODING_QUOTEDPRINTABLE;

        return $html;
    }


    /**
     * creates an email message
     *
     * @param  string $markup HTML content for email message
     * @param  string $textContent plain-text content for email message
     * @return Message
     */
    public function createEmailMessage(string $markup = '', string $textContent = '') : Message
    {
        $html = $this->createHtmlPart($markup);
        if (! $textContent) {
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
        $message->getHeaders()->addHeaderLine(
            'X-Sent-By',
            'InterpretersOffice https://interpretersoffice.org'
        );

        return $message;
    }

    /**
     * adds headers to message
     *
     * @param Message      $message
     * @param Entity\Person $person
     * @param Array        $contact
     */
    public function setMessageHeaders(Message $message, Entity\Person $person, Array $contact)
    {
        $message->setFrom($contact['email'], $contact['organization_name'])
            ->setTo($person->getEmail(), $person->getFullname())
            ->addCc($contact['email'], $contact['organization_name']);

        return $message;
    }


    /**
     * gets email layout
     *
     * @param  string $template
     * @return ViewModel
     */
    public function getEmailLayout($template = 'interpreters-office/email/layout')
    {
        if (! $this->layout) {
            $this->layout = (new ViewModel())->setTemplate($template);
        }
        return $this->layout;
    }
}
