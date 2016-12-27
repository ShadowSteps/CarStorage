<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 19:41
 */

namespace Shadows\CarStorage\Crawler\Index;


use Shadows\CarStorage\Core\Communication\JobRegistration;

class JobExtractResult
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

}