<?php

namespace CMuench\PHPStormDownloader\Command;

use CMuench\PHPStormDownloader\Repository\Eap;
use CMuench\PHPStormDownloader\Repository\HttpSource;
use CMuench\PHPStormDownloader\Repository\Stable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class DownloadCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('download')
            ->setDescription('Download PhpStorm to target folder and create a Symlink "PhpStorm" to latest version.')
            ->addArgument('target-folder', InputArgument::OPTIONAL, 'Target Folder for Installation', $_SERVER['HOME'] . '/opt')
            ->addOption('symlink-name', NULL, InputOption::VALUE_OPTIONAL, 'Filename of the symlink', 'PhpStorm')
            ->addOption('download', null, InputOption::VALUE_NONE, 'Download even if target version already exists')
            ->addOption('stable', null, InputOption::VALUE_NONE, 'Use stable release, not EAP')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $targetFolder = $input->getArgument('target-folder');
        if (!is_dir($targetFolder)) {
            throw new \LogicException('Folder ' . $targetFolder . ' does not exist.');
        }

        $symlinkName = basename($input->getOption('symlink-name'));

        $forceDownload = (bool) $input->getOption('download');

        $stable = (bool) $input->getOption('stable');

        $repo = $stable ? new Stable() : new Eap();

        $repo->initialize($output);

        foreach ($repo->getSources() as $source) {
            $this->install($output, $targetFolder, $symlinkName, $forceDownload, $source);
            break;
        }
    }

    private function install(OutputInterface $output, $targetFolder, $symlinkName, $forceDownload, HttpSource $source)
    {
        $version = $source->getVersion();

        $extractedFolder = 'PhpStorm-' . $version;

        if (is_dir($targetFolder . '/' . $extractedFolder) && false === $forceDownload) {
            $output->writeln(
                sprintf(
                    '<comment>Phpstorm <info>%s</info> already exists, skipping download.</comment>', $version
                )
            );
        } else {
            $output->write(
                sprintf(
                    '<comment>Download %s </comment><info>%s</info><comment>... </comment>', $source->getName(), $version
                )
            );

            $downloadProcess = new Process(sprintf("wget %s -O phpstorm.tar.gz", escapeshellarg($source->getUrl())));
            $downloadProcess->setTimeout(3600);
            $this->runProcess($output, $downloadProcess);
            $output->writeln('<info>OK</info>');

            $output->write('<comment>Extracting... </comment>');
            $extractProcess = new Process(
                sprintf(
                    'tar xfz phpstorm.tar.gz; rm phpstorm.tar.gz; mv %1$s %2$s'
                    , escapeshellarg($extractedFolder), escapeshellarg($targetFolder)
                )
            );
            $this->runProcess($output, $extractProcess);
            $output->writeln('<info>OK</info>');
        }

        $output->writeln(
            sprintf(
                '<comment>Symlinking <info>%s</info> to <info>%s</info> in <info>%s</info></comment>.',
                $symlinkName, $extractedFolder, $targetFolder
            )
        );
        $command = sprintf(
            'cd %2$s && (test -e %3$s && unlink %3$s ; ln -s -f %1$s %3$s)',
            escapeshellarg($extractedFolder),
            escapeshellarg($targetFolder),
            escapeshellarg($symlinkName)
        );
        $linkProcess = new Process($command);
        $this->runProcess($output, $linkProcess);
    }

    /**
     * @param OutputInterface $output
     * @param Process $process
     */
    private function runProcess(OutputInterface $output, Process $process)
    {
        if ($output->getVerbosity() >= $output::VERBOSITY_VERBOSE) {
            $output->writeln(
                sprintf('<comment>Execute: <info>%s</info></comment>', $process->getCommandLine())
            );
        }

        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }
    }
}
