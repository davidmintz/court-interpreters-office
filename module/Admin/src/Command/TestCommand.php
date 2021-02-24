<?php

namespace InterpretersOffice\Admin\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestCommand extends Command
{
    
    private $em;

    public function __construct(Object $em)
    {
        //echo "Hello...";
        $this->em = $em;
        parent::__construct();
    }

    public function configure()
    {

    }
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input,$output);
        $io->success("Test has succeeded, you bitch. Congratulations! We have a ".get_class($this->em));
        return 0;
    }
}


