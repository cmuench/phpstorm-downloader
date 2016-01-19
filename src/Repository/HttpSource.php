<?php
/**
 * Created by PhpStorm.
 * User: mot
 * Date: 18.01.16
 * Time: 19:54
 */

namespace CMuench\PHPStormDownloader\Repository;


class HttpSource
{
    private $name;
    private $version;
    private $url;


    /**
     * HttpSource constructor.
     *
     * @param $name
     * @param $version
     * @param $url
     */
    public function __construct($name, $version, $url)
    {
        $this->name = $name;
        $this->version = $version;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}
