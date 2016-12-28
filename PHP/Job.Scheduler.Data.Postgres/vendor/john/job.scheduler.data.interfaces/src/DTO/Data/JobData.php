<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 14:06
 */

namespace Shadows\CarStorage\Data\Interfaces\DTO\Data;


class JobData
{
    /**
     * @var int
     */
    private $jobType;
    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $hash;
    /**
     * @var bool
     */
    private $locked;
    /**
     * @var \DateTime
     */
    private $dateAdded;


    /**
     * @return int
     */
    public function getJobType(): int
    {
        return $this->jobType;
    }

    /**
     * @param int $jobType
     */
    public function setJobType(int $jobType)
    {
        $this->jobType = $jobType;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash(string $hash)
    {
        $this->hash = $hash;
    }

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * @param bool $locked
     */
    public function setLocked(bool $locked)
    {
        $this->locked = $locked;
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

}