<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BenchmarkCommand extends Command
{
    protected static $defaultName = 'http:benchmark';

    /**
     *
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Benchmark one or more URLs')
            ->addOption(
                'threads',
                't',
                InputOption::VALUE_OPTIONAL,
                'The number of threads to run',
                1
            )
            ->addOption(
                'max-rpm',
                'r',
                InputOption::VALUE_OPTIONAL,
                'Max requests/minute',
                null
            )
            ->addArgument(
                'url-or-filepath',
                InputOption::VALUE_REQUIRED,
                'A single URL or path to a local file containing a newline-separated list of URLs.'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $urlOrFilePath = $input->getArgument('url-or-filepath');

        return 0;
    }
}