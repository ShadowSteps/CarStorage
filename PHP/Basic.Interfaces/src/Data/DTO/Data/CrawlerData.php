<?php

namespace AdSearchEngine\Interfaces\Data\DTO\Data;


class CrawlerData
{
    /**
     * @var string[]
     */
    private $allowedIPsList;
    /**
     * @var \DateTime
     */
    private $lastCall;


    /**
     * @return \string[]
     */
    public function getAllowedIPsList(): array
    {
        return $this->allowedIPsList;
    }

    /**
     * @param \string[] $allowedIPsList
     */
    public function setAllowedIPsList(array $allowedIPsList)
    {
        $this->allowedIPsList = $allowedIPsList;
    }

    /**
     * @return \DateTime
     */
    public function getLastCall(): \DateTime
    {
        return $this->lastCall;
    }

    /**
     * @param \DateTime $lastCall
     */
    public function setLastCall(\DateTime $lastCall)
    {
        $this->lastCall = $lastCall;
    }

    
}