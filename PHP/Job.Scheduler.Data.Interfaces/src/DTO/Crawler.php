<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 5/2/2017
 * Time: 4:14 PM
 */

namespace Shadows\CarStorage\Data\Interfaces\DTO;


class Crawler
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var string[]
     */
    private $allowedIPsList;
    /**
     * @var \DateTime
     */
    private $dateAdded;
    /**
     * @var \DateTime
     */
    private $lastCall;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

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
    public function getDateAdded(): \DateTime
    {
        return $this->dateAdded;
    }

    /**
     * @param \DateTime $dateAdded
     */
    public function setDateAdded(\DateTime $dateAdded)
    {
        $this->dateAdded = $dateAdded;
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