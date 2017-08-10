<?php

namespace AdSearchEngine\Core\Data\Postgres\Sets;


use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityNotFoundException;
use AdSearchEngine\Interfaces\Data\DTO\Data\JobData;
use AdSearchEngine\Interfaces\Data\DTO\Job;
use AdSearchEngine\Interfaces\Data\Sets\IJobSet;
use AdSearchEngine\Core\Data\Postgres\Entities\Crawlers;
use AdSearchEngine\Core\Data\Postgres\Entities\Jobs;
use AdSearchEngine\Core\Data\Postgres\Exceptions\NoJobsFoundException;
use AdSearchEngine\Core\Data\Postgres\Sets\Base\BaseSet;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Component\Config\Definition\Exception\Exception;

class JobSet extends BaseSet implements IJobSet
{

    private function ConvertEntityToDTO(Jobs $entity): Job {
        $job = new Job();
        $job->setDateAdded($entity->getDateAdded());
        $job->setHash($entity->getHash());
        $job->setId($entity->getId());
        $job->setJobType($entity->getType());
        $job->setLocked($entity->getLocked());
        $job->setUrl($entity->getUrl());
        $job->setAddedByCrawlerId($entity->getCrawlerId()->getId());
        if (!is_null($entity->getDoneBy()))
            $job->setDoneByCrawlerId($entity->getDoneBy()->getId());
        return $job;
    }

    private function FillEntityFromData(JobData $data, Jobs &$entity){
        $entity->setUrl($data->getUrl());
        $entity->setLocked($data->isLocked());
        $entity->setHash($data->getHash());
        $entity->setType($data->getJobType());
        $entity->setDateAdded($data->getDateAdded());
        /**
         * @var $addedCrawler Crawlers
         */
        $addedCrawler = $this->getManager()->find("AdSearchEngine\Core\Data\Postgres\Entities\Crawlers", $data->getAddedByCrawlerId());
        $entity->setCrawlerId($addedCrawler);
        if (!is_null($data->getDoneByCrawlerId())) {
            /**
             * @var $doneByCrawler Crawlers
             */
            $doneByCrawler = $this->getManager()->find("AdSearchEngine\Core\Data\Postgres\Entities\Crawlers", $data->getDoneByCrawlerId());
            $entity->setDoneBy($doneByCrawler);
        }
    }


    public function Add(JobData $data): string
    {
        $entity = new Jobs();
        $data->setDateAdded(new \DateTime());
        $this->FillEntityFromData($data, $entity);
        $this->getManager()
            ->persist($entity);
        return $entity->getId();
    }

    public function Edit(string $id, JobData $data)
    {
        $entity = $this->getManager()
            ->find('AdSearchEngine\Core\Data\Postgres\Entities\Jobs', $id);
        if (!($entity instanceof Jobs))
            throw new Exception("Entity is not of type Jobs!");
        $this->FillEntityFromData($data, $entity);
    }

    public function Delete(string $id)
    {
        $entity = $this->getManager()
            ->find('AdSearchEngine\Core\Data\Postgres\Entities\Jobs', $id);
        $this->getManager()
            ->remove($entity);
    }

    public function GetNextFreeJob(): Job
    {
        $rsm = new ResultSetMappingBuilder($this->getManager());
        $rsm->addRootEntityFromClassMetadata('AdSearchEngine\Core\Data\Postgres\Entities\Jobs', "j");
        $query = $this->getManager()->createNativeQuery(
            'SELECT * FROM jobs as j WHERE random() < 0.01 and locked = false limit 1', $rsm);
        $result = $query->getResult();
        if (!count($result))
            throw new NoJobsFoundException("No jobs found!");
        $entity = array_pop($result);
        return $this->ConvertEntityToDTO($entity);
    }

    public function GetByHash(string $hash): Job
    {
        $criteria = new Criteria();
        $criteria->where($criteria->expr()->eq('hash', $hash));
        $criteria->orderBy(['dateAdded'=>'DESC']);
        $result = $this->getManager()
            ->getRepository('AdSearchEngine\Core\Data\Postgres\Entities\Jobs')
            ->matching($criteria);
        if ($result->isEmpty())
            throw new EntityNotFoundException();
        $entity = $result->first();
        return $this->ConvertEntityToDTO($entity);
    }

    public function LockJob(string $id)
    {
        $entity = $this->getManager()
            ->find('AdSearchEngine\Core\Data\Postgres\Entities\Jobs', $id);
        if (!($entity instanceof Jobs))
            throw new Exception("Entity is not of type Jobs!");
        $entity->setLocked(true);
    }

    public function UnlockJob(string $id)
    {
        $entity = $this->getManager()
            ->find('AdSearchEngine\Core\Data\Postgres\Entities\Jobs', $id);
        if (!($entity instanceof Jobs))
            throw new Exception("Entity is not of type Jobs!");
        $entity->setLocked(false);
        $entity->setDateAdded(new \DateTime());
    }

    public function GetById(string $id): Job
    {
        $entity = $this->getManager()
            ->find('AdSearchEngine\Core\Data\Postgres\Entities\Jobs', $id);
        if (!($entity instanceof Jobs))
            throw new EntityNotFoundException("Entity is not of type Jobs!");
        return $this->ConvertEntityToDTO($entity);
    }
}