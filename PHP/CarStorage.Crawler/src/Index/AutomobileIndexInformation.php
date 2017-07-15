<?php

namespace CarStorage\Crawler\Index;


use AdSearchEngine\Interfaces\Index\AdIndexInformation;

class AutomobileIndexInformation extends AdIndexInformation
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $title;
    /**
     * @var string
     */
    private $keywords;
    /**
     * @var string
     */
    private $description;
    /**
     * @var string
     */
    private $url;
    /**
     * @var float
     */
    private $price;
    /**
     * @var string
     */
    private $currency;
    /**
     * @var \DateTime
     */
    private $date;
    /**
     * @var int
     */
    private $kilometers;
    /**
     * @var int|null
     */
    private $cluster;

    public function __construct(string $id, string $title, string $description, string $url, float $price, string $currency, \DateTime $year, int $kilometers, string $keywords = '', int $cluster = null)
    {
        parent::__construct($id, $cluster);
        $this->title = $title;
        $this->description = $description;
        $this->url = $url;
        $this->keywords = $keywords;
        $this->price = $price;
        $this->currency = $currency;
        $this->date = $year;
        $this->kilometers = $kilometers;
        $this->cluster = $cluster;
    }

    public function addKeywords(array $keyword) {
        $this->keywords .= ";" . implode(" ; ", $keyword);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return \string
     */
    public function getKeywords(): string
    {
        return $this->keywords;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @return int
     */
    public function getKilometers(): int
    {
        return $this->kilometers;
    }

    /**
     * @return int|null
     */
    public function getCluster()
    {
        return $this->cluster;
    }

    /**
     * @param int|null $cluster
     */
    public function setCluster($cluster)
    {
        $this->cluster = $cluster;
    }
}