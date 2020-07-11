#!/usr/bin/env php
<?php /** bin/sdny/quarterly-report-to-json.php */

/*
§ 370.10.40 Report Contents
(a) The report should contain a summary of the preceding quarter’s use of
interpreters for court proceedings and probation or pretrial services
events under the Court Interpreters Act. Each district has the discretion
to determine whether the district court and the probation or pretrial
services office will provide separate or combined reports.
(b) Each report should indicate the number of docketed events in which
certified staff, certified contractor, or otherwise-qualified interpreters
were provided, by language. An event for the purpose of this reporting
requirement is: one interpreter, one date, one case number, equals one
event.

(c) For specific reporting requirements and format, see: JNet’s Reporting
Requirements page.
http://jnet.ao.dcn/court-services/district-clerks-offices/court-interpreting/reporting-requirements
*/
use Laminas\Mvc\Application as App;
chdir(dirname(dirname(__DIR__)));
require 'vendor/autoload.php';

$laminasApp = App::init(require 'config/application.config.php');
$container = $laminasApp->getServiceManager();

$today = getdate();
$month = $today['mon'];
$year = $today['year'];

// default is previous quarter. command-line options to come later
if (in_array($month,[1,2,3])) {
    $year -= 1;
    $from = "$year-01-01";
    $to = "$year-12-31";
} elseif (in_array($month,[4,5,6])) {
    $from = "$year-01-01";
    $to   = "$year-03-31";
} elseif (in_array($month,[7,8,9])) {
    $from = "$year-04-01";
    $to   = "$year-06-30";
} else {
    $from = "$year-07-01";
    $to   = "$year-09-30"; 
}
// test with different data
// $year = 2019;$from = "$year-10-01"; $to = "$year-12-31"; 

/** @var Doctrine\ORM\EntityManager $em */
$em = $container->get('entity-manager');
/** @var \PDO $db */
$db = $em->getConnection()->getWrappedConnection();
$sql = 'SELECT DISTINCT e.id,e.date, e.docket, i.id AS interpreter_id , i.lastname AS interpreter, 
    IF(h.name LIKE "%contract%","contractor","staff") AS status,t.name AS evt_type,
    IF (e.cancellation_reason_id IS NULL, 0, 1) AS cancelled,
    c.category,l.name AS language, cr.abbreviation AS cred
    FROM events e JOIN interpreters_events ie  ON e.id = ie.event_id 
    JOIN people i ON i.id = ie.interpreter_id JOIN hats h ON i.hat_id = h.id
    JOIN interpreters_languages il ON il.interpreter_id = i.id
    JOIN language_credentials cr ON cr.id = il.credential_id
    JOIN event_types t ON e.event_type_id = t.id
    JOIN languages l ON l.id = e.language_id
    JOIN event_categories c ON t.category_id = c.id
    WHERE e.date BETWEEN :from AND :to 
    AND c.category IN ("in","out")
    ORDER BY l.name, e.date, e.docket';
$stmt = $db->prepare($sql);
$stmt->execute([':from'=>$from,':to'=>$to]);

// actual data
$data = [];

// actual numbers
$totals = [
    'Spanish' => [ 'in' => 0, 'out' => 0],
    'staff'   => [ 'in' => 0, 'out' => 0],
    'non-Spanish' => [ 'in' => 0, 'out' => 0],
];

$template =  [
    'in_events' => 0, 'in_cost' => 0, 'in_expenses' => 0,
    'out_cost' => 0, 'out_expenses' => 0 , 'out_events' => 0,
];
// fantasy numbers for the AO
$report = [
    'Spanish/staff'=> ['in_events'=>0, 'out_events'=>0],
    'Spanish/contract'=> $template,
];

$total_events = $stmt->rowCount(); // true total events
while ($r = $stmt->fetch(\PDO::FETCH_ASSOC)) {

    $key = sprintf('%s.%s.%d.%s',$r['date'],$r['docket']?:'no-docket',$r['interpreter_id'],$r['language']);
    if (key_exists($key,$data)) {
        $data[$key][] = $r;
    } else {
        $data[$key] = [ $r ];
    }
    if ($r['language'] == 'Spanish') {  // Spanish
        $totals['Spanish'][$r['category']]++;
        if ($r['category'] == 'in') {            
            if ($r['status'] == 'staff') {
                $totals['staff']['in']++;                
            }
        } else {            
            if ($r['status'] == 'staff') {
                $totals['staff']['out']++;                
            }
        }
    } else { // non-Spanish
        $totals['non-Spanish'][$r['category']]++;
    }

    if (! key_exists($r['language'],$report)) {
        $report[$r['language']] = $template;
    }
}
$stmt->closeCursor();

// now iterate the $data to populate $report
foreach ($data as $key =>$array)  {
    $language = $array[0]['language'];    
    $cats = array_unique(array_column($array,'category'));
    if (count($cats) == 1) {
        $category = $cats[0];            
    } else {
        $category = "in";
    }
    $report[$language]["{$category}_events"]++;
    if ($array[0]['status'] == "staff") {
        $report['Spanish/staff']["{$category}_events"]++;
    } elseif ($language == "Spanish") {
        $report['Spanish/contract']["{$category}_events"]++;
    }
}
$report['_summary'] = [
    'dates' => ['from'=>$from,'to'=>$to],
    'bullshit total events' => count($data),
    'actual total events' => $total_events,
    'actual totals' => $totals
];

echo json_encode($report);
