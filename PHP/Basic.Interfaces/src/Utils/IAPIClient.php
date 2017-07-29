<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/15/2017
 * Time: 6:41 PM
 */

namespace AdSearchEngine\Interfaces\Utils;


use AdSearchEngine\Interfaces\Communication\Crawler\Request\CrawlerExtractJobResultInformation;
use AdSearchEngine\Interfaces\Communication\Crawler\Request\CrawlerHarvestJobResultInformation;
use AdSearchEngine\Interfaces\Communication\Crawler\Response\CrawlerStateInformation;
use AdSearchEngine\Interfaces\Communication\Search\SearchQuery;

interface IAPIClient
{
    public function GetNextCrawlerJob(): CrawlerStateInformation;
    public function RegisterNewCrawlerJobs(CrawlerHarvestJobResultInformation $registration): CrawlerStateInformation;
    public function AddDocument(CrawlerExtractJobResultInformation $information): CrawlerStateInformation;
    public function DeleteDocument(string $id): CrawlerStateInformation;
    public function Search(SearchQuery $query): array;
}