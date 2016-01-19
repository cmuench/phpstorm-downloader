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

        $forceDownload = (bool) $input->getOption('download');

        $stable = (bool) $input->getOption('stable');

        $repo = $stable ? new Stable() : new Eap();

        $repo->initialize($output);

        foreach ($repo->getSources() as $source) {
            $this->install($output, $targetFolder, $forceDownload, $source);
            break;
        }
    }

    private function install(OutputInterface $output, $targetFolder, $forceDownload, HttpSource $source)
    {
        $version = $source->getVersion();

        $extractedFolder = 'PhpStorm-' . $version;

        if (is_dir($targetFolder . '/' . $extractedFolder) && false === $forceDownload) {
            $output->writeln(
                sprintf(
                    '<comment>Phpstorm <info>%s</info> already exists, skipping download..</comment>', $version
                )
            );
        } else {
            $output->write(
                sprintf(
                    '<comment>Download %s </comment><info>%s</info><comment>...</comment>', $source->getName(), $version
                )
            );

            $downloadProcess = new Process(sprintf("wget %s -O phpstorm.tar.gz", escapeshellarg($source->getUrl())));
            $downloadProcess->setTimeout(3600);
            $downloadProcess->run();

            $output->writeln(' <info>OK</info>');
            if (!$downloadProcess->isSuccessful()) {
                throw new \RuntimeException($downloadProcess->getErrorOutput());
            }


            $output->write('<comment>Extracting...</comment>');
            $extractProcess = new Process(
                sprintf(
                    'tar xfz phpstorm.tar.gz; rm phpstorm.tar.gz; mv %1$s %2$s'
                    , escapeshellarg($extractedFolder), escapeshellarg($targetFolder)
                )
            );
            $extractProcess->run();
            $output->writeln(' <info>OK</info>');
            if (!$extractProcess->isSuccessful()) {
                throw new \RuntimeException($extractProcess->getErrorOutput());
            }

        }

        $output->write('<comment>Linking...</comment>');

        $linkProcess = new Process(
            sprintf(
                'cd %2$s; ln -s -f %1$s PhpStorm', escapeshellarg($extractedFolder), escapeshellarg($targetFolder)
            )
        );
        $linkProcess->run();
        $output->writeln(' <info>OK</info>');
        if (!$linkProcess->isSuccessful()) {
            throw new \RuntimeException($linkProcess->getErrorOutput());
        }

    }
}
