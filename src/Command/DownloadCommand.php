<?php

namespace CMuench\PHPStormDownloader\Command;

use Goutte\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Process\Process;

class DownloadCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('download')
            ->setDescription('Download PhpStorm to target folder and create a Symlink "PhpStorm" to latest version.')
            ->addArgument('target-folder', InputArgument::OPTIONAL, 'Target Folder for Installation', $_SERVER['HOME'] . '/opt')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $url = 'http://confluence.jetbrains.com/display/PhpStorm/PhpStorm+Early+Access+Program';
        $targetFolder = $input->getArgument('target-folder');
        if (!is_dir($targetFolder)) {
            throw new \LogicException('Folder ' . $targetFolder . ' does not exist.');
        }

        try {
            $client = new Client();
            $output->write('<comment>Request PhpStorm EAP: </comment>');
            $crawler = $client->request('GET', $url);
            $output->writeln('<info>OK</info>');
            $crawler->filter('a.external-link')
                ->reduce(function (Crawler $node, $i) {
                    return strstr($node->text(), '.tar.gz');
                })
                ->each(function ($node) use ($targetFolder, $output) {
                    $downloadUrl = $node->attr('href');
                    if (preg_match('/PhpStorm-EAP-(\d+\.\d+)\.tar\.gz/i', $node->text(), $matches)) {
                        $phpStormVersion = $matches[1];
                        $output->writeln('<comment>Found EAP Version   : </comment><info>' . $phpStormVersion . '</info>');

                        $output->write('<comment>Download            : </comment>');
                        $downloadProcess = new Process("wget $downloadUrl -O phpstorm.tar.gz");
                        $downloadProcess->setTimeout(3600);
                        $downloadProcess->run();

                        $output->writeln('<info>OK</info>');
                        if (!$downloadProcess->isSuccessful()) {
                            throw new \RuntimeException($downloadProcess->getErrorOutput());
                        }

                        $extractedFolder = 'PhpStorm-' . $phpStormVersion;
                        $output->write('<comment>Extract             : </comment>');
                        $extractProcess = new Process("tar xfz phpstorm.tar.gz; rm phpstorm.tar.gz; mv $extractedFolder $targetFolder; cd $targetFolder; rm PhpStorm; ln -s $extractedFolder PhpStorm");
                        $extractProcess->run();
                        $output->writeln('<info>OK</info>');
                        if (!$extractProcess->isSuccessful()) {
                            throw new \RuntimeException($extractProcess->getErrorOutput());
                        }
                    }
                });
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}