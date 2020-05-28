<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;


class BenchmarkRequestCommand extends Command
{
    protected static $defaultName = 'http:benchmark-request';

    /**
     *
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->addArgument(
                'url',
                InputOption::VALUE_REQUIRED,
                'A single URL to be tested.'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getArgument('url');

        $client = HttpClient::create();
        $response = $client->request('GET', $url, ['max_redirects' => 0]);

        $output->write(
            $response->getStatusCode() . "\t" .
            $response->getInfo('namelookup_time') . "\t" .
            $response->getInfo('redirect_count') . "\t" .
            $response->getInfo('redirect_time') . "\t" .
            $response->getInfo('connect_time') . "\t" .
            $response->getInfo('start_time') . "\t" .
            $response->getInfo('starttransfer_time') . "\t" .
            $response->getInfo('total_time') . "\t" .
            $response->getInfo('request_size')
        );

        return 0;
    }
}