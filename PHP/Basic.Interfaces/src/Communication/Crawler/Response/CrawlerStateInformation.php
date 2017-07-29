<?php

namespace AdSearchEngine\Interfaces\Communication\Crawler\Response;

use AdSearchEngine\Interfaces\Communication\JSONCommunicationObject;

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