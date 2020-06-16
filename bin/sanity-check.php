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
    
    // the latest holiday in the database should be at least 90 days in the future
    $date_str = $db->query('SELECT date FROM court_closings ORDER BY date DESC LIMIT 1')
        ->fetchColumn();
    $latest = new \DateTime($date_str);
    $diff =(new \DateTime())->diff($latest);
    $format = $diff->invert ? '%R%d' : '%d';
    $days = $diff->format($format);
    if ($days < 90) {
        
        $config = $container->get('config');
        $service = new Service\BatchEmailService($config['mail']);
        $transport = $service->getTransport();
        $mailer = new Swift_Mailer($transport);
        $message = new Swift_Message('InterpretersOffice database maintenance required');
        $content = 
        "<p>Hello,</p>

        <p>This automated message is to let you know that some maintenance needs to 
        be done with your <code>InterpretersOffice</code> application database. The latest date in your list of official 
        Court holidays should be no less than 90 days into the future, but your latest date is only $days days from now. 
        Please log in and go to your application's <code>/admin/court-closings</code> page to insert holidays.</p>";
        $view = new ViewModel(['content'=>$content]);
        $view->setTemplate('interpreters-office/email/layout.phtml');
        $html = $container->get('ViewRenderer')->render($view);
        $text = strip_tags($content);
        $address = [$config['mail']['from_address']=>$config['mail']['from_entity']];
        $message->setBody($text)->addPart($html,'text/html')->setFrom($address)
            ->setTo($address);
        $transport->send($message);
        $container->get('log')->warn("Latest holiday in the database is $days days away. We emailed a notice to {$config['mail']['from_address']}",['channel'=>'data-maintenance']);

    }

} catch (\Exception $e) {
    $filename = basename(__FILE__);
    $container->get('log')->err(sprintf( "%s failed with error message: %s\n",$filename,$e->getMessage()));
    exit(1);

}
