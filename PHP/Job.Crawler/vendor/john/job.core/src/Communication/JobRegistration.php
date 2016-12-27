<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 15:00
 */

namespace Shadows\CarStorage\Core\Communication;


use JsonSerializable;

class JobRegistration extends JSONObject implements JsonSerializable
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var array
     */
    private $newJobs = [];

    /**
     * JobRegistration constructor.
     * @param $id
     * @param array $newJobs
     */
    public function __construct(string $id, array $newJobs)
    {
        $this->id = $id;
        foreach ($newJobs as $newJob) {
            $this->addNewJob($newJob);
        }
    }


    /**
     * @return mixed
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @param JobInformation $job
     */
    public function addNewJob(JobInformation $job) {
        $this->newJobs[] = $job;
    }

    /**
     * @return JobInformation[]
     */
    public function getNewJobs(): array
    {
        return $this->newJobs;
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
            "Id" => $this->getId(),
            "NewJobs" => $this->getNewJobs()
        ];
    }
}