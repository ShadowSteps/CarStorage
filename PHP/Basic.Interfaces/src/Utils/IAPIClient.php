<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/15/2017
 * Time: 6:41 PM
 */

namespace AdSearchEngine\Interfaces\Utils;


use AdSearchEngine\Interfaces\Crawler\Communication\Request\CrawlerExtractJobResultInformation;
use AdSearchEngine\Interfaces\Crawler\Communication\Request\CrawlerHarvestJobResultInformation;
use AdSearchEngine\Interfaces\Crawler\Communication\Response\CrawlerStateInformation;

interface IAPIClient
{
    public function GetNextCrawlerJob(): CrawlerStateInformation;
    public function RegisterNewCrawlerJobs(CrawlerHarvestJobResultInformation $registration): CrawlerStateInformation;
    public function AddDocument(CrawlerExtractJobResultInformation $information): CrawlerStateInformation;
    public function DeleteDocument(string $id): CrawlerStateInformation;
}