<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 14:55
 */

namespace Shadows\CarStorage\Core\Communication;


class JobInformation extends JobStatus implements \JsonSerializable
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $url;
    /**
     * @var integer
     */
    private $jobType;

    /**
     * JobInformation constructor.
     * @param string $id
     * @param string $url
     * @param int $jobType
     */
    public function __construct($id, $url, $jobType)
    {
        parent::__construct(true);
        $this->id = $id;
        $this->url = $url;
        $this->jobType = $jobType;
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
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function getJobType(): int
    {
        return $this->jobType;
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
            "Status" => $this->isStatus(),
            "Id" => $this->getId(),
            "JobType" => $this->getJobType(),
            "Url" => $this->getUrl()
        ];
    }
}