<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 5/2/2017
 * Time: 6:06 PM
 */

namespace AdSearchEngine\Interfaces\Repository;


interface IRepository
{
    public function FinishJob(string $jobId, string $crawlerId);
}