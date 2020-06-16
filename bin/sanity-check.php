#!/usr/bin/env php
<?php

declare(strict_types=1);
use InterpretersOffice\Admin\Service;
use Laminas\Mvc\Application as App;
use Laminas\View\Model\ViewModel;

chdir(dirname(__DIR__));
require dirname(__DIR__) . '/vendor/autoload.php';

$laminasApp = App::init(require dirname(__DIR__) . '/config/application.config.php');
$container = $laminasApp->getServiceManager();

try {
    $params = (require 'config/autoload/doctrine.local.php')['doctrine']['connection']['orm_default']['params'];
    $dsn = sprintf('%s:host=%s;dbname=%s',explode('_',$params['driver'])[1],$params['host'],$params['dbname']
        );
    $db = new \PDO($dsn,$params['user'],$params['password'],[
        PDO::ATTR_ERRMODE =>  PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
    ]);
    
    // the latest holiday in the database should be at least 12 weeks in the future
    $date_str = $db->query('SELECT date FROM court_closings ORDER BY date DESC LIMIT 1')
        ->fetchColumn();
    $latest = new \DateTime($date_str);
    $diff = $latest->diff(new \DateTime());
    $days = $diff->format('%R%d');
    if ($days < 90) {
       
        $service = new Service\BatchEmailService($container->get('config')['mail']);
        $transport = $service->getTransport();
        $mailer = new Swift_Mailer($transport);
        $message = new Swift_Message('Your InterpretersOffice database needs maintenance');
        $view = new ViewModel(['content'=>'Not enough holidays in your database']);
        $view->setTemplate('interpreters-office/email/layout.phtml');
        $html = $container->get('ViewRenderer')->render($view);
        
        // $layout->setVariable('content', $this->viewRenderer->render($view));
        // $content = $this->viewRenderer->render($layout);
        // echo get_class($transport);
        // $message = $service->createEmailMessage('<p>You need to add more Court holidays to your database</p>','');
        // $message->setTo('david@davidmintz.org');
        // $message->setFrom('webmaster@davidmintz.org');
        // $transport = $service->getMailTransport();
        //$transport->send($message);
    }

} catch (\Exception $e) {
    $filename = basename(__FILE__);
    exit(sprintf( "%s failed with error message: %s\n",$filename,$e->getMessage()));

}
