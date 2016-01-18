<?php

namespace CMuench\PHPStormDownloader\Repository;

use Goutte\Client;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Http
 *
 * @package CMuench\PHPStormDownloader\Repository
 */
abstract class Http
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array|HttpSource[]
     */
    protected $sources;

    /**
     * @param OutputInterface $output
     */
    final public function initialize(OutputInterface $output)
    {
        $output->write(sprintf('<comment>Request %s: </comment>', $this->name));

        $this->sources = [];

        $client = new Client();

        $this->fillSources($output, $client);
    }
    
    /**
     * @param OutputInterface $output
     * @param Client $client
     */
    abstract protected function fillSources(OutputInterface $output, Client $client);

    /**
     * @return array|HttpSource[]
     */
    public function getSources()
    {
        return $this->sources;
    }

}
