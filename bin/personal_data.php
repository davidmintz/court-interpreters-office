#!/usr/bin/env php
<?php
/**  fetches sensitive data from old interpreters database and insert into new */

require __DIR__.'/../vendor/autoload.php';

use Zend\Crypt\BlockCipher;
use Zend\Crypt\Symmetric\Openssl;
use Zend\Console\Getopt;

$opts = new Getopt(
    [
        'check-only' => "just read values from \"office\" database",
        'skip-check' => "run import without reading back out",
        'help|h'       => "print usage statement",
    ]   
);
if ($opts->getOption('help')) { echo $opts->getUsageMessage(); exit(); }
$skipCheck = $opts->getOption('skip-check');
$checkOnly = $opts->getOption('check-only');

$db_params = parse_ini_file(getenv('HOME').'/.my.cnf');
$ciphers = parse_ini_file(__DIR__.'/../config/dev.local.conf',true);

$cipher = new BlockCipher(new Openssl);
$cipher->setKey($ciphers['office']['cipher']);

$old_db = new PDO('mysql:host=localhost;dbname=dev_interpreters', $db_params['user'], $db_params['password']);

$new_db = new PDO('mysql:host=localhost;dbname=office', $db_params['user'], $db_params['password']);


// test it 
function read_it_back($db_connection,$cipher)
{
    $select_2 = $db_connection->query('SELECT id, ssn, dob FROM interpreters WHERE ssn IS NOT NULL or dob IS NOT NULL');
    $select_2->execute();
    while ($row = $select_2->fetchObject()) {
        $ssn =  $row->ssn ? $cipher->decrypt($row->ssn) : NULL;
        $dob = $row->dob ? $cipher->decrypt($row->dob) : NULL;
        printf("id %d; dob: %s, ssn: %s\n",$row->id,$dob,$ssn);
        
        if ($dob && ! preg_match('/^\d{4}-\d{2}-\d{2}$/',$dob)) {
            echo "! MALFORMED dob for id $row->id: $dob\n";
        }
        $just_digits = preg_replace('/\D/','',$ssn);
        if ($ssn && strlen($just_digits) != 9) {
            echo "! possible MALFORMED ssn for id $row->id: $ssn\n";
        }
    }
    
}
if ($checkOnly) {
    read_it_back($new_db,$cipher); 
    exit;
}



$select = $old_db->prepare('SELECT interp_id AS id, AES_DECRYPT(ssn,:cipher) AS ssn, '
        . 'AES_DECRYPT(dob,:cipher) AS dob FROM interpreters '
        . 'WHERE ssn IS NOT NULL OR dob IS NOT NULL');

$update = $new_db->prepare("UPDATE interpreters SET ssn = :ssn, dob = :dob WHERE id = :id");

$result = $select->execute([':cipher'=>$ciphers['dev_interpreters']['key']]);
$total = $select->rowCount();
if (! $result) {
    exit(print_r($old_db->errorInfo(),true));
}
$i = 0;
echo "\n";
while ($data = $select->fetch(\PDO::FETCH_OBJ)) {
    
    $ssn = $data->ssn ? $cipher->encrypt($data->ssn) : null;
    $dob = $data->dob ?$cipher->encrypt($data->dob) : null;
    printf("running update on  %d of %d\r",++$i, $total);
    
    if (! $update->execute(['ssn'=>$ssn,'dob'=>$dob,'id'=>$data->id])) {
         exit(print_r($old_db->errorInfo(),true));
    }
    
}
// one more
$update->execute([
    'ssn'=>$cipher->encrypt('075-46-2139'),
    'dob'=> $cipher->encrypt('1957-03-22'),
    'id' => 117,
]);
echo "\ndone.\n";
if (! $skipCheck) {
    read_it_back($new_db,$cipher); 
}



