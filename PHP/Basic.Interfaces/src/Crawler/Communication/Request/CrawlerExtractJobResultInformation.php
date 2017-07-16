<?php

namespace AdSearchEngine\Interfaces\Crawler\Communication\Request;

use AdSearchEngine\Interfaces\Index\AdIndexInformation;
use AdSearchEngine\Interfaces\Crawler\Communication\JSONCommunicationObject;

class CrawlerExtractJobResultInformation extends JSONCommunicationObject
{
    public static $adIndexInformationType = "AdSearchEngine\\Interfaces\\Index\\AdIndexInformation";
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

    public static function fromSTD(\stdClass $object) {
        $harvestJob = CrawlerHarvestJobResultInformation::fromSTD($object->harvestJobResultInformation);
        $className = self::$adIndexInformationType;
        $adIndexInformation = call_user_func("\\".$className."::fromSTD", $object->adIndexInformation);
        return new self($harvestJob, $adIndexInformation);
    }
}