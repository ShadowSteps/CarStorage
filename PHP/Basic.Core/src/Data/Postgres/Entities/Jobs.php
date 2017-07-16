<?php

namespace AdSearchEngine\Core\Data\Postgres\Entities;

use Doctrine\Mapping as ORM;

/**
 * Jobs
 *
 * @Table(name="jobs")
 * @Entity
 */
class Jobs
{
    /**
     * @var string
     *
     * @Column(name="id", type="guid", nullable=false)
     * @Id
     * @GeneratedValue(strategy="UUID")
     */
    private $id = 'uuid_generate_v4()';

    /**
     * @var integer
     *
     * @Column(name="type", type="integer", nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @Column(name="url", type="text", nullable=false)
     */
    private $url;

    /**
     * @var string
     *
     * @Column(name="hash", type="text", nullable=false)
     */
    private $hash;

    /**
     * @var boolean
     *
     * @Column(name="locked", type="boolean", nullable=false)
     */
    private $locked = false;

    /**
     * @var \DateTime
     *
     * @Column(name="date_added", type="datetime", nullable=false)
     */
    private $dateAdded;

    /**
     * @ManyToOne(targetEntity="Crawlers")
     * @JoinColumn(name="crawler_id", referencedColumnName="id")
     */
    private $crawlerId;

    /**
     * @ManyToOne(targetEntity="Crawlers")
     * @JoinColumn(name="done_by", referencedColumnName="id")
     */
    private $doneBy;


    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return Jobs
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Jobs
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set hash
     *
     * @param string $hash
     *
     * @return Jobs
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set locked
     *
     * @param boolean $locked
     *
     * @return Jobs
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Get locked
     *
     * @return boolean
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * Set dateAdded
     *
     * @param \DateTime $dateAdded
     *
     * @return Jobs
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    /**
     * Get dateAdded
     *
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * @return Crawlers
     */
    public function getCrawlerId(): Crawlers
    {
        return $this->crawlerId;
    }

    /**
     * @param Crawlers $crawlerId
     */
    public function setCrawlerId(Crawlers $crawlerId)
    {
        $this->crawlerId = $crawlerId;
    }

    /**
     * @return Crawlers|null
     */
    public function getDoneBy()
    {
        return $this->doneBy;
    }

    /**
     * @param Crawlers $doneBy
     */
    public function setDoneBy(Crawlers $doneBy)
    {
        $this->doneBy = $doneBy;
    }


}

