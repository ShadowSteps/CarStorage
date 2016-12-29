<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 19:40
 */

namespace Shadows\CarStorage\Crawler\Index;


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
     * @var string[]
     */
    private $keywords = [];
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
     * JobIndexInformation constructor.
     * @param string $id
     * @param string $title
     * @param \string[] $keywords
     * @param string $description
     */
    public function __construct(string $id, string $title, string $description, string $url, float $price, string $currency, array $keywords =[])
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->url = $url;
        foreach ($keywords as $word)
            $this->addKeyword($word);
        $this->price = $price;
        $this->currency = $currency;
    }

    public function addKeyword(string $keyword) {
        $this->keywords[] = $keyword;
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
     * @return \string[]
     */
    public function getKeywords(): array
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
                    "currency" => $this->getCurrency()
                ]
            ],
            "commit" => [
                "waitSearcher" => false
            ]
        ];
    }
}