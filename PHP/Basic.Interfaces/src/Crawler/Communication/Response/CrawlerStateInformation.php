<?php

namespace AdSearchEngine\Interfaces\Crawler\Communication\Response;

use AdSearchEngine\Interfaces\Crawler\Communication\JSONCommunicationObject;

class CrawlerStateInformation extends JSONCommunicationObject
{
    private $active = false;

    public function __construct(bool $active = false)
    {
        $this->active = $active;
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}