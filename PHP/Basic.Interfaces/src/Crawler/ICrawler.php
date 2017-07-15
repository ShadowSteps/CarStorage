<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/15/2017
 * Time: 6:43 PM
 */

namespace AdSearchEngine\Interfaces\Crawler;


use AdSearchEngine\Interfaces\Crawler\Communication\Response\CrawlerJobInformation;

interface ICrawler
{
    public function doCrawlerJob(CrawlerJobInformation $jobInformation);
}