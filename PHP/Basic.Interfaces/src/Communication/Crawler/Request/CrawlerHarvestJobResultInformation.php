<?php

namespace AdSearchEngine\Interfaces\Communication\Crawler\Request;

use AdSearchEngine\Interfaces\Communication\JSONCommunicationObject;
use AdSearchEngine\Interfaces\Communication\Crawler\Response\CrawlerJobInformation;
use AdSearchEngine\Interfaces\Communication\Utils\StdClassExtractor;

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

    public static function fromSTD(\stdClass $object) {
        $extractor = new StdClassExtractor($object);
        $id = $extractor->GetString("id");
        $jobsArray = [];
        $newJobs = $object->newJobs;
        foreach ($newJobs as $job) {
            $jobsArray[] = CrawlerJobInformation::fromSTD($job);
        }
        return new self($id, $jobsArray);
    }
}