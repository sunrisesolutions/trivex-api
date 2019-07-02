<?php

namespace App\Command;

use App\Service\AppService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SnsCreateCommand extends Command
{
    protected static $defaultName = 'app:sns:create';

    protected $service;

    public function __construct(string $name = null, AppService $service)
    {
        parent::__construct($name);
        $this->service = $service;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if (empty($env = $input->getOption('env'))) {
            $env = 'dev';
        } else {
            $io->note(sprintf('You passed an option: %s', $env));
        }

        $r = $this->service->initiateTopics($env);

        $io->note(json_encode($r));

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}
