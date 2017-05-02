<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 14:08
 */

namespace Shadows\CarStorage\Data\Interfaces;


use Shadows\CarStorage\Data\Interfaces\Sets\ICrawlerSet;
use Shadows\CarStorage\Data\Interfaces\Sets\IJobSet;

interface IJobSchedulerContext
{
    public function getJobSet(): IJobSet;
    public function getCrawlerSet(): ICrawlerSet;
    public function SaveChanges();
}