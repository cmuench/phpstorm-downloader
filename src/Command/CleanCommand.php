<?php

namespace CMuench\PHPStormDownloader\Command;

use Goutte\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class CleanCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('clean')
            ->addArgument('target-folder', InputArgument::OPTIONAL, 'Target Folder for Installation', $_SERVER['HOME'] . '/opt')
            ->setDescription('Clean all not symlinked PHPStorm folders in target folder');
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $foundLinkTarget = '';
        $targetFolder = $input->getArgument('target-folder');
        if (!is_dir($targetFolder)) {
            throw new \LogicException('Folder ' . $targetFolder . ' does not exist.');
        }

        $finder = Finder::create()
            ->directories()
            ->name('PhpStorm')
            ->depth(0)
            ->in($targetFolder);
        foreach ($finder as $dir) {
            /* @var $dir \Symfony\Component\Finder\SplFileInfo */
            $foundLinkTarget = $dir->getLinkTarget();
            $output->writeln('<info>Found Symlink to current version </info><comment>' . $foundLinkTarget . '</comment>');
            break;
        }

        if (empty($foundLinkTarget)) {
            $output->writeln('<info>Please run download before any clean operation</info>');
            return 1;
        }

        $finder = Finder::create()
            ->directories()
            ->name('PhpStorm-*')
            ->notName($foundLinkTarget)
            ->in($targetFolder)
            ->depth(0);

        $filesystem = new Filesystem();
        foreach ($finder as $dir) {
            $output->writeln('<info>Remove directory</info> <comment>' . $dir->getRelativePathname() . '</comment>');
            $filesystem->remove(array($dir));
        }
    }
}