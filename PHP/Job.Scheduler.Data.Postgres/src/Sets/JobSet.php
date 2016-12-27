<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 14:27
 */

namespace Shadows\CarStorage\Data\Postgres\Sets;


use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityNotFoundException;
use Shadows\CarStorage\Data\Interfaces\DTO\Data\JobData;
use Shadows\CarStorage\Data\Interfaces\DTO\Job;
use Shadows\CarStorage\Data\Interfaces\Sets\IJobSet;
use Shadows\CarStorage\Data\Postgres\Entities\Jobs;
use Shadows\CarStorage\Data\Postgres\Exceptions\NoJobsFoundException;
use Shadows\CarStorage\Data\Postgres\Sets\Base\BaseSet;
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
        return $job;
    }

    private function FillEntityFromData(JobData $data, Jobs &$entity){
        $entity->setUrl($data->getUrl());
        $entity->setLocked($data->isLocked());
        $entity->setHash($data->getHash());
        $entity->setType($data->getJobType());
    }


    public function Add(JobData $data): string
    {
        $entity = new Jobs();
        $this->FillEntityFromData($data, $entity);
        $entity->setDateAdded(new \DateTime());
        $this->getManager()
            ->persist($entity);
        return $entity->getId();
    }

    public function Edit(string $id, JobData $data)
    {
        $entity = $this->getManager()
            ->find('Shadows\CarStorage\Data\Postgres\Entities\Jobs', $id);
        if (!($entity instanceof Jobs))
            throw new Exception("Entity is not of type Jobs!");
        $this->FillEntityFromData($data, $entity);
    }

    public function Delete(string $id)
    {
        $entity = $this->getManager()
            ->find('Shadows\CarStorage\Data\Postgres\Entities\Jobs', $id);
        $this->getManager()
            ->remove($entity);
    }

    public function GetNextFreeJob(): Job
    {
        $criteria = new Criteria();
        $criteria->where($criteria->expr()->eq('locked', false));
        $criteria->orderBy(['dateAdded'=>'DESC']);
        $result = $this->getManager()
            ->getRepository('Shadows\CarStorage\Data\Postgres\Entities\Jobs')
            ->matching($criteria);
        if ($result->isEmpty())
            throw new NoJobsFoundException("No jobs found!");
        $entity = $result->first();
        return $this->ConvertEntityToDTO($entity);
    }

    public function GetByHash(string $hash): Job
    {
        $criteria = new Criteria();
        $criteria->where($criteria->expr()->eq('hash', $hash));
        $criteria->orderBy(['dateAdded'=>'DESC']);
        $result = $this->getManager()
            ->getRepository('Shadows\CarStorage\Data\Postgres\Entities\Jobs')
            ->matching($criteria);
        if ($result->isEmpty())
            throw new EntityNotFoundException();
        $entity = $result->first();
        return $this->ConvertEntityToDTO($entity);
    }

    public function LockJob(string $id)
    {
        $entity = $this->getManager()
            ->find('Shadows\CarStorage\Data\Postgres\Entities\Jobs', $id);
        if (!($entity instanceof Jobs))
            throw new Exception("Entity is not of type Jobs!");
        $entity->setLocked(true);
    }

    public function UnlockJob(string $id)
    {
        $entity = $this->getManager()
            ->find('Shadows\CarStorage\Data\Postgres\Entities\Jobs', $id);
        if (!($entity instanceof Jobs))
            throw new Exception("Entity is not of type Jobs!");
        $entity->setLocked(false);
    }
}