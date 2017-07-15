<?php

namespace AdSearchEngine\Interfaces\Data\Sets;

use AdSearchEngine\Interfaces\Data\DTO\Data\JobData;
use AdSearchEngine\Interfaces\Data\DTO\Job;

interface IJobSet
{
    public function Add(JobData $data): string;
    public function Edit(string $id, JobData $data);
    public function Delete(string $id);
    public function GetByHash(string $hash): Job;
    public function GetNextFreeJob(): Job;
    public function LockJob(string $id);
    public function UnlockJob(string $id);
    public function GetById(string $id): Job;
}