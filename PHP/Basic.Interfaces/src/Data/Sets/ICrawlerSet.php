<?php

namespace AdSearchEngine\Interfaces\Data\Sets;


use AdSearchEngine\Interfaces\Data\DTO\Data\CrawlerData;

interface ICrawlerSet
{
    public function Edit(string $id, CrawlerData $data);
    public function RegisterCall(string $id);
    public function Exists(string $id): bool;
}