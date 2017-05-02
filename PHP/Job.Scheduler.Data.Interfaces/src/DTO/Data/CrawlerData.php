<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 5/2/2017
 * Time: 4:14 PM
 */

namespace Shadows\CarStorage\Data\Interfaces\DTO\Data;


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