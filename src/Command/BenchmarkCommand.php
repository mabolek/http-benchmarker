<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

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
                'url-or-file-path',
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
        $urlOrFilePath = $input->getArgument('url-or-file-path');

        $urls = [];

        if (strpos($urlOrFilePath, 'http') === 0) {
            $urls[] = $urlOrFilePath;
        } elseif(file_exists($urlOrFilePath)) {
            $urls = explode("\n", file_get_contents($urlOrFilePath));
        } else {
            throw new InvalidArgumentException('Invalid URL or file path: "' . $urlOrFilePath . '"', 1590600467);
        }

        $phpPath = (new PhpExecutableFinder())->find();

        $threads = [];
        $threadIds = range(0, $input->getOption('threads')-1);
        $processCounter = 0;

        do {
            while (count($threads) < $input->getOption('threads') && count($urls) > 0) {
                $processCounter++;
                $url = array_shift($urls);
                $process = new Process([$phpPath, 'bin/console', 'http:benchmark-request', $url]);
                $process->start();

                $threads[array_values(array_diff($threadIds, array_keys($threads)))[0]] = [
                    'url' => $url,
                    'process' => $process,
                    'counter' => $processCounter
                ];
            }

            do {
                usleep(250000);

                $allProcessesAreRunning = true;
                foreach ($threads as $key => &$thread) {
                    if (!$thread['process']->isRunning()) {
                        $allProcessesAreRunning = false;

                        $output->writeln($thread['counter'] . "\t" . ($key + 1) . "\t" . date("Y-m-d H:i:s") . "\t" . $thread['url'] . "\t" . $thread['process']->getOutput());

                        unset($threads[$key]);
                    }
                }

                if (!$allProcessesAreRunning) {
                    break;
                }
            } while (true);
        } while(count($urls) > 0);

        return 0;
    }
}