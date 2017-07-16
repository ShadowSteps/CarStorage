<?php
namespace AdSearchEngine\Core\Repository;

use AdSearchEngine\Interfaces\Data\DTO\Data\JobData;
use AdSearchEngine\Interfaces\Data\IAdSearchEngineContext;
use AdSearchEngine\Interfaces\Repository\IRepository;


class Repository implements IRepository
{
    /**
     * @var IAdSearchEngineContext
     */
    private $context;

    /**
     * Repository constructor.
     * @param IAdSearchEngineContext $context
     */
    public function __construct(IAdSearchEngineContext $context)
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
     * @return IAdSearchEngineContext
     */
    public function getContext(): IAdSearchEngineContext
    {
        return $this->context;
    }
}