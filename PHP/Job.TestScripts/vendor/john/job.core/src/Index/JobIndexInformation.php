<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 19:40
 */

namespace Shadows\CarStorage\Core\Index;


use JsonSerializable;

class JobIndexInformation implements JsonSerializable
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
        $this->id = $id;
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
        $this->keywords .= ";" . implode(";", $keyword);
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
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
           "add" => [
                "doc" => [
                    "id" => $this->getId(),
                    "title" => $this->getTitle(),
                    "keywords" => $this->getKeywords(),
                    "description" => $this->getDescription(),
                    "url" => $this->getUrl(),
                    "price" => $this->getPrice(),
                    "currency" => $this->getCurrency(),
                    "year" => $this->getDate()->format("Y-m-d\\TH:i:s\\Z"),
                    "km" => $this->getKilometers(),
                    "cluster" => (is_null($this->getCluster()) ? -1 : $this->getCluster())
                ]
            ],
            "commit" => [
                "waitSearcher" => false
            ]
        ];
    }

    function jsonSerializeClean()
    {
        return
            [
                "id" => $this->getId(),
                "title" => $this->getTitle(),
                "keywords" => $this->getKeywords(),
                "description" => $this->getDescription(),
                "url" => $this->getUrl(),
                "price" => $this->getPrice(),
                "currency" => $this->getCurrency(),
                "year" => $this->getDate()->format("Y-m-d\\TH:i:s\\Z"),
                "km" => $this->getKilometers(),
                "cluster" => (is_null($this->getCluster()) ? -1 : $this->getCluster())
            ];
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