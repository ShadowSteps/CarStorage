<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 19:41
 */

namespace Shadows\CarStorage\Core\Communication;


use Shadows\CarStorage\Core\Index\JobIndexInformation;

class JobExtractResult extends JSONObject
{
    /**
     * @var JobRegistration
     */
    private $jobRegistration;
    /**
     * @var JobIndexInformation
     */
    private $jobIndexInformation;

    /**
     * JobExtractResult constructor.
     * @param JobRegistration $jobRegistration
     * @param JobIndexInformation $jobIndexInformation
     */
    public function __construct(JobRegistration $jobRegistration, JobIndexInformation $jobIndexInformation)
    {
        $this->jobRegistration = $jobRegistration;
        $this->jobIndexInformation = $jobIndexInformation;
    }

    /**
     * @return JobRegistration
     */
    public function getJobRegistration(): JobRegistration
    {
        return $this->jobRegistration;
    }

    /**
     * @return JobIndexInformation
     */
    public function getJobIndexInformation(): JobIndexInformation
    {
        return $this->jobIndexInformation;
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
            "jobRegistration" => $this->getJobRegistration()->jsonSerialize(),
            "jobIndexInformation" => $this->getJobIndexInformation()->jsonSerialize()
        ];
    }
}