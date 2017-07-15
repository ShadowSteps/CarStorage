<?php

namespace CarStorage\Data\Postgres\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Jobs
 *
 * @ORM\Table(name="crawlers")
 * @ORM\Entity
 */
class Crawlers
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="guid", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id = 'uuid_generate_v4()';

    /**
     * @var string
     *
     * @ORM\Column(name="allowed_ip", type="text", nullable=false)
     */
    private $allowed_ip = '127.0.0.1';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=false)
     */
    private $dateAdded = 'now()';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_call", type="datetime", nullable=true)
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
     * @return string
     */
    public function getAllowedIp(): string
    {
        return $this->allowed_ip;
    }

    /**
     * @param string $allowed_ip
     */
    public function setAllowedIp(string $allowed_ip)
    {
        $this->allowed_ip = $allowed_ip;
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
     * @return \DateTime|null
     */
    public function getLastCall()
    {
        return $this->lastCall;
    }

    /**
     * @param \DateTime $lastCall|null
     */
    public function setLastCall($lastCall)
    {
        $this->lastCall = $lastCall;
    }


}

