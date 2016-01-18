<?php

namespace CMuench\PHPStormDownloader\Repository;

use Goutte\Client;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Eap
 *
 * @package CMuench\PHPStormDownloader\Repository
 */
class Eap extends Http
{
    protected $name = 'PhpStorm EAP';

    private $url = 'https://confluence.jetbrains.com/display/PhpStorm/PhpStorm+Early+Access+Program';

    private $pattern = '/PhpStorm-EAP-(\d+\.\d+)(?:-custom-jdk-linux)?\.tar\.gz/i';

    /**
     * @param OutputInterface $output
     * @param Client $client
     */
    protected function fillSources(OutputInterface $output, Client $client)
    {

        $name = $this->name;
        $url = $this->url;
        $pattern = $this->pattern;

        $crawler = $client->request('GET', $url);
        $output->writeln('<info>OK</info>');
        $crawler->filter('a.external-link')
            ->each(function (Crawler $node) use ($pattern, $output, $name) {
                $url = $node->attr('href');
                $linkText = $node->text();
                if (!preg_match($pattern, $linkText, $matches)) {
                    return;
                }

                $phpStormVersion = $matches[1];
                $output->writeln('<comment>Found ' . $name . ' Version: </comment><info>' . $phpStormVersion . '</info>');

                $source = new HttpSource($name, $phpStormVersion, $url);
                $this->sources[] = $source;
            });
    }
}
