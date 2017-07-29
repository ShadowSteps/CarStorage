<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 19:37
 */

namespace AdSearchEngine\Core\Crawler\Plugin;

use AdSearchEngine\Interfaces\Communication\Crawler\Request\CrawlerExtractJobResultInformation;
use AdSearchEngine\Interfaces\Communication\Crawler\Request\CrawlerHarvestJobResultInformation;
use AdSearchEngine\Interfaces\Communication\Crawler\Response\CrawlerJobInformation;

interface ICrawlerPlugin
{
    public function doHarvestJob(CrawlerJobInformation $information, \DOMDocument $document): CrawlerHarvestJobResultInformation;
    public function doExtractJob(CrawlerJobInformation $information, \DOMDocument $document): CrawlerExtractJobResultInformation;
}