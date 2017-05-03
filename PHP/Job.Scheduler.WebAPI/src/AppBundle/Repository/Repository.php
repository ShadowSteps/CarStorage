<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 5/2/2017
 * Time: 6:07 PM
 */

namespace AppBundle\Repository;


use AppBundle\Interfaces\Repository\IRepository;
use Shadows\CarStorage\Data\Interfaces\DTO\Crawler;
use Shadows\CarStorage\Data\Interfaces\DTO\Data\JobData;
use Shadows\CarStorage\Data\Interfaces\DTO\Job;
use Shadows\CarStorage\Data\Interfaces\IJobSchedulerContext;

class Repository implements IRepository
{
    /**
     * @var IJobSchedulerContext
     */
    private $context;

    /**
     * Repository constructor.
     * @param IJobSchedulerContext $context
     */
    public function __construct(IJobSchedulerContext $context)
    {
        $this->context = $context;
    }


    public function FinishJob(string $jobId, string $crawlerId)
    {
        $job = $this->getContext()
            ->getJobSet()
            ->GetById($jobId);
        $data = new JobData();
        $data->setJobType($job->getJobType());
        $data->setUrl($job->getUrl());
        $data->setHash($job->getHash());
        $data->setLocked($job->isLocked());
        $data->setAddedByCrawlerId($job->getAddedByCrawlerId());
        $data->setDoneByCrawlerId($crawlerId);
        $data->setDateAdded(new \DateTime());
        $this->getContext()
            ->getJobSet()
            ->Edit($jobId, $data);
    }

    /**
     * @return IJobSchedulerContext
     */
    public function getContext(): IJobSchedulerContext
    {
        return $this->context;
    }
}