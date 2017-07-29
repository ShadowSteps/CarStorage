<?php

namespace AdSearchEngine\Interfaces\Communication\Crawler\Response;

use AdSearchEngine\Interfaces\Communication\Crawler\Response\CrawlerStateInformation;
use AdSearchEngine\Interfaces\Communication\Crawler\Enum\JobType;

class CrawlerJobInformation extends CrawlerStateInformation
{
    private $id;
    private $url;
    private $jobType;

    public function __construct(string $id, string $url, int $jobType)
    {
        JobType::isJobType($jobType);
        parent::__construct(true);
        $this->id = $id;
        $this->url = $url;
        $this->jobType = $jobType;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getJobType(): int
    {
        return $this->jobType;
    }
}