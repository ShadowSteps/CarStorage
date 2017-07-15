<?php

namespace AdSearchEngine\Interfaces\Data\DTO;


class Job
{
    /**
     * @var string
     */
    private $id;
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
     * @var string
     */
    private $addedByCrawlerId;
    /**
     * @var string
     */
    private $doneByCrawlerId = null;

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

    /**
     * @return string
     */
    public function getAddedByCrawlerId(): string
    {
        return $this->addedByCrawlerId;
    }

    /**
     * @param string $addedByCrawlerId
     */
    public function setAddedByCrawlerId(string $addedByCrawlerId)
    {
        $this->addedByCrawlerId = $addedByCrawlerId;
    }

    /**
     * @return string|null
     */
    public function getDoneByCrawlerId()
    {
        return $this->doneByCrawlerId;
    }

    /**
     * @param string $doneByCrawlerId
     */
    public function setDoneByCrawlerId(string $doneByCrawlerId)
    {
        $this->doneByCrawlerId = $doneByCrawlerId;
    }

}