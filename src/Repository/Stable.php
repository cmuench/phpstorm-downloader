<?php

namespace CMuench\PHPStormDownloader\Repository;
use Goutte\Client;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Eap
 *
 * @package CMuench\PHPStormDownloader\Repository
 */
class Stable extends Http
{
    protected $name = 'PhpStorm stable';

    protected $url = 'https://data.services.jetbrains.com/products/releases?code=PS&latest=true&type=release&_=999';

    public function fillSources(OutputInterface $output, Client $client)
    {
        $url = $this->url;
        $name = $this->name;

        $client->request('GET', $url);

        /** @var Response $response */
        $response = $client->getResponse();

        $data = json_decode($response->getContent(), true, 64);

        if (!$data) {
            return;
        }

        $version =  &$data['PS'][0]['build'];
        $link = &$data['PS'][0]['downloads']['linux']['link'];

        if (!strlen($version) || !strlen($link)) {
            return;
        }

        $source = new HttpSource($name, $version, $link);
        $this->sources[] = $source;
    }
}
