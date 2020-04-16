<?php /** module/Admin/src/Service/BatchEmailService.php */

declare(strict_types=1);

namespace InterpretersOffice\Admin\Service;

use Swift_SmtpTransport;
use Swift_Message;
use Swift_Mailer;
use Swift_Transport;

/**
 * service for sending batch email
 * 
 * A work in progress as we transition away from the inordinately 
 * unwieldy Laminas\Mail
 */
class BatchEmailService 
{
    /**
     * @var array
     */
    private $config;

    /**
     * constructor
     */
    public function __construct(array $config)
    {
        $this->config = $config;

    }

    /**
     * gets transport
     * 
     * @return Swift_Transport
     */
    public function getTransport() : Swift_Transport
    {

        $config = $this->config['transport_options']['options'];
        $transport = new Swift_SmtpTransport(
            $config['host'],$config['port'],'ssl'
        );
        $transport->setUserName($config['connection_config']['username'])
            ->setPassword($config['connection_config']['password']);
            
        return $transport;

    }

    /**
     * sends test email
     * 
     * @param string $address
     * @return int
     */
    public function test(string $address = null) : int
    {
        $config = $this->config['transport_options']['options'];
        $transport = new Swift_SmtpTransport(
            $config['host'],$config['port'],'ssl'
        );
        $transport->setUserName($config['connection_config']['username'])
            ->setPassword($config['connection_config']['password']);

        $message = new Swift_Message("test one two Swift_Mailer");
        $message->setBody('My <em>amazing</em> body', 'text/html');

        // Add alternative parts with addPart()
        $message->addPart('My amazing body in plain text', 'text/plain');
        $message->setFrom($this->config['from_address'])   
            ->setTo($address ?? 'david@davidmintz.org');
        $mailer = new Swift_Mailer($transport);
        
        $result = $mailer->send($message);

        return $result;
            
    }
    
}