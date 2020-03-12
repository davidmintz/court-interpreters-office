#!/usr/bin/env php
<?php

require(__DIR__.'/../../vendor/autoload.php');

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

use Laminas\Crypt\BlockCipher;
use Laminas\Crypt\Symmetric\Openssl;

class PersonalDataImportCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'setup:import:personal-data';

    /**
     * @todo database names as command-line options
     * 
     * @return void
     */
    protected function configure()
    {
       $this
            ->addOption('source-db', 's',InputOption::VALUE_REQUIRED,'Name of the source database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $source_db = $input->getOption('source-db') ?? 'dev_interpreters';
        
        $helper = $this->getHelper('question');
        $question = new Question('Enter the encryption cipher for the target db:  ');
        $question->setHidden(true)->setHiddenFallback(false);        
        $cipher = $helper->ask($input, $output, $question);
        

        $question2 = new Question('Enter the cipher for the old db:  ');
        $question2->setHidden(true)->setHiddenFallback(false);
        
        $old_cipher = $helper->ask($input, $output, $question2);        
        $db = require(__DIR__.'/connect.php');
        $source = $input->getOption('source-db') ?? 'dev_interpreters';
        $db->exec("use $source");
        $select = $db->prepare('SELECT interp_id AS id, AES_DECRYPT(ssn,:cipher) AS ssn, '
        . 'AES_DECRYPT(dob,:cipher) AS dob FROM interpreters '
        . 'WHERE (ssn IS NOT NULL OR dob IS NOT NULL)');        
        $result = $select->execute([':cipher'=>$old_cipher]);
        if (! $result) {
            exit(print_r($db->errorInfo(),true));
        }
        $total = $select->rowCount();
        echo "total found: $total\n";
        $block_cipher = new BlockCipher(new Openssl);
        $block_cipher->setKey($cipher);
        $db->exec("use office");
        $update = $db->prepare("UPDATE interpreters SET ssn = :ssn, dob = :dob WHERE id = :id");
        $i = 0;
        while($row = $select->fetch()) {
            if (!$row['dob'] && !$row['ssn']) {
                continue;
            }
            $params = [
                ':ssn' =>$row['ssn'] ? $block_cipher->encrypt($row['ssn']) : null, 
                ':dob' =>$row['dob'] ? $block_cipher->encrypt($row['dob']) : null, 
                ':id'  => $row['id'],
            ];
            $outcome = $update->execute($params);
            printf ("completed: %d\r",++$i);            
        }
        echo "\ndone\n";
        return 0;
    }   
}


$application = new Application();
$application->add(new PersonalDataImportCommand);
$application->run();


