<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 19:37
 */

namespace AdSearchEngine\Core\Crawler\Plugin;

use AdSearchEngine\Interfaces\Crawler\Communication\Request\CrawlerExtractJobResultInformation;
use AdSearchEngine\Interfaces\Crawler\Communication\Request\CrawlerHarvestJobResultInformation;
use AdSearchEngine\Interfaces\Crawler\Communication\Response\CrawlerJobInformation;

interface ICrawlerPlugin
{
    public function doHarvestJob(CrawlerJobInformation $information, \DOMDocument $document): CrawlerHarvestJobResultInformation;
    public function doExtractJob(CrawlerJobInformation $information, \DOMDocument $document): CrawlerExtractJobResultInformation;
}