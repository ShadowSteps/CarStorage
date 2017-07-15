<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/15/2017
 * Time: 9:10 PM
 */

namespace AdSearchEngine\Interfaces\WebAPI\Controller;


use AdSearchEngine\Interfaces\Crawler\Communication\Request\CrawlerExtractJobResultInformation;

interface IDocumentController
{
    public function addAction(CrawlerExtractJobResultInformation $jobResultInformation);
    public function deleteAction(string $documentId);
}