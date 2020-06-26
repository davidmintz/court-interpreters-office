#!/usr/bin/env php
<?php /** bin/sanity-check.php */

/**
 * Checks to ensure that court-closings in the database are being maintained.
 * Suitable for cron.
 * 
 * If there is a public API or other means of programmatically reading official
 * Court holidays, david@davidmintz.org would love to hear about it. I've written 
 * screen-scrapers to parse the holidays out of public web pages, but those are 
 * too prone to breakage.
 * 
 */

 declare(strict_types=1);

use InterpretersOffice\Admin\Service;
use Laminas\Mvc\Application as App;
use Laminas\View\Model\ViewModel;
use InterpretersOffice\Entity;
use InterpretersOffice\Admin\Service\Log\Writer as DbWriter;

chdir(dirname(__DIR__));
require dirname(__DIR__) . '/vendor/autoload.php';

$laminasApp = App::init(require dirname(__DIR__) . '/config/application.config.php');
$container = $laminasApp->getServiceManager();
$log = $container->get('log');
$log->addWriter($container->get(DbWriter::class));

try {
    /** @var Doctrine\ORM\EntityManager $em */
    $em = $container->get('entity-manager');
    /** @var InterpretersOffice\Entity\Repository\CourtClosingRepository $repo */
    $repo = $em->getRepository(Entity\CourtClosing::class);
    $result = $repo->sanityCheck();
    if (!$result['sanity']) {
        
        $config = $container->get('config');
        $service = new Service\BatchEmailService($config['mail']);
        $transport = $service->getTransport();
        $mailer = new Swift_Mailer($transport);
        $message = new Swift_Message('InterpretersOffice database maintenance required');
        $content = 
        "<p>Hello,</p>

        <p>This automated message is to let you know that some maintenance needs to 
        be done with your <code>InterpretersOffice</code> application database. {$result['message']}
        Please consult your Court's official list of holidays, go to your application's 
        <code>/admin/court-closings</code> page, and enter <em>all</em> the holidays.</p>";
        $view = new ViewModel(['content'=>$content]);
        $view->setTemplate('interpreters-office/email/layout.phtml');
        $html = $container->get('ViewRenderer')->render($view);
        $text = strip_tags($content);
        $address = [$config['mail']['from_address']=>$config['mail']['from_entity']];
        $message->setBody($text)->addPart($html,'text/html')->setFrom($address)        
            ->setTo($address);
        $transport->send($message);
        $log->warn("Court-closing sanity check failed with message: \"{$result['message']}\" We emailed a notice to {$config['mail']['from_address']}",['channel'=>'data-maintenance']);
    } else {
        $log->info(basename(__FILE__) .': court-closing sanity check OK',['channel'=>'data-maintenance']);
    }

} catch (\Exception $e) {
    $filename = basename(__FILE__);
    $log->err(sprintf( "%s failed with error message: %s\n",$filename,$e->getMessage()));
    exit(1);

}
