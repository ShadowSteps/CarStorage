<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 14:05
 */

namespace Shadows\CarStorage\Data\Interfaces\Sets;


use Shadows\CarStorage\Data\Interfaces\DTO\Data\JobData;
use Shadows\CarStorage\Data\Interfaces\DTO\Job;

interface IJobSet
{
    public function Add(JobData $data): string;
    public function Edit(string $id, JobData $data);
    public function Delete(string $id);
    public function GetByHash(string $hash): Job;
    public function GetNextFreeJob(): Job;
    public function LockJob(string $id);
    public function UnlockJob(string $id);
}