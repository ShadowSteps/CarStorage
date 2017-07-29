<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/15/2017
 * Time: 9:03 PM
 */

namespace AdSearchEngine\Interfaces\WebAPI\Controller;


use AdSearchEngine\Interfaces\Communication\Crawler\Request\CrawlerHarvestJobResultInformation;

interface IJobSchedulerController
{
    public function finishJobAction(CrawlerHarvestJobResultInformation $jobResultInformation);
    public function getNextJobAction();
}