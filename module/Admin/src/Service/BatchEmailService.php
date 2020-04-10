<?php

declare(strict_types=1);

namespace InterpretersOffice\Admin\Service;

// use \Swift_SmtpTransport;
// use \Swift_Mailer;
// use \Swift_Message;


class BatchEmailService 
{
    var $config;

    public function __construct(array $config)
    {
        $this->config = $config;

    }
    public function getTransport()
    {

        $config = $this->config['transport_options']['options'];
        $transport = new \Swift_SmtpTransport(
            $config['host'],$config['port'],'ssl'
        );
        $transport->setUserName($config['connection_config']['username'])
            ->setPassword($config['connection_config']['password']);
            
        return $transport;

    }
    public function test()
    {
        $config = $this->config['transport_options']['options'];
        $transport = new \Swift_SmtpTransport(
            $config['host'],$config['port'],'ssl'
        );
        $transport->setUserName($config['connection_config']['username'])
            ->setPassword($config['connection_config']['password']);

        $message = new \Swift_Message("test one two Swift_Mailer");
        $message->setBody('My <em>amazing</em> body', 'text/html');

        // Add alternative parts with addPart()
        $message->addPart('My amazing body in plain text', 'text/plain');
        $message->setFrom($this->config['from_address'])   
            ->setTo('david@davidmintz.org');
        $mailer = new \Swift_Mailer($transport);
        
        $result = $mailer->send($message);

        return $result;
            
    }
    /*
     'mail' => [
        'transport' => Smtp::class,
        'transport_options' => [
            'class' => SmtpOptions::class,
            'options' => [
                'name'     => 'smtp.fastmail.com',
                'host'     => 'smtp.fastmail.com',
                'port'     => 465,
                'connection_class'  => 'login',

                'connection_config' => [
                    'username' => 'mintz@vernontbludgeon.com',
                    'password' => 'hmb6a4w9yvwqgjhq',
                    'ssl'  => 'ssl',
                ],
            ],
        ],
        'from_address' => 'interpreters@sdnyinterpreters.org',
        'from_entity' => 'Interpreters Office',
    ], */

}