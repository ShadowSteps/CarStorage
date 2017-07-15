<?php

namespace AdSearchEngine\Interfaces\Crawler\Communication\Request;

use AdSearchEngine\Interfaces\Crawler\Communication\JSONCommunicationObject;
use AdSearchEngine\Interfaces\Crawler\Communication\Response\CrawlerJobInformation;

class CrawlerHarvestJobResultInformation extends JSONCommunicationObject
{
    private $id;
    private $newJobs = [];

    public function __construct(string $id, array $newJobs)
    {
        $this->id = $id;
        foreach ($newJobs as $newJob) {
            $this->addNewJob($newJob);
        }
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function addNewJob(CrawlerJobInformation $job) {
        $this->newJobs[] = $job;
    }

    /**
     * @return CrawlerJobInformation[]
     */
    public function getNewJobs(): array
    {
        return $this->newJobs;
    }
}