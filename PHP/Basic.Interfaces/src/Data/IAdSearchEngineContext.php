<?php

namespace AdSearchEngine\Interfaces\Data;


use AdSearchEngine\Interfaces\Data\Sets\ICrawlerSet;
use AdSearchEngine\Interfaces\Data\Sets\IJobSet;

interface IAdSearchEngineContext
{
    public function getJobSet(): IJobSet;
    public function getCrawlerSet(): ICrawlerSet;
    public function SaveChanges();
}