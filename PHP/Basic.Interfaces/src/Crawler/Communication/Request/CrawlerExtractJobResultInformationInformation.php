<?php

namespace AdSearchEngine\Interfaces\Crawler\Communication\Request;

use AdSearchEngine\Interfaces\Index\AdIndexInformation;
use AdSearchEngine\Interfaces\Crawler\Communication\JSONCommunicationObject;

class CrawlerExtractJobResultInformation extends JSONCommunicationObject
{
    private $harvestJobResultInformation;
    private $adIndexInformation;

    public function __construct(CrawlerHarvestJobResultInformation $jobRegistration, AdIndexInformation $jobIndexInformation)
    {
        $this->harvestJobResultInformation = $jobRegistration;
        $this->adIndexInformation = $jobIndexInformation;
    }

    public function getHarvestJobResultInformation(): CrawlerHarvestJobResultInformation
    {
        return $this->harvestJobResultInformation;
    }

    public function getAdIndexInformation(): AdIndexInformation
    {
        return $this->adIndexInformation;
    }
}