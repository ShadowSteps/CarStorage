<?php

namespace AdSearchEngine\Core\Data\Postgres\Sets;

use AdSearchEngine\Interfaces\Data\DTO\Data\CrawlerData;
use AdSearchEngine\Interfaces\Data\Sets\ICrawlerSet;
use AdSearchEngine\Core\Data\Postgres\Entities\Crawlers;
use AdSearchEngine\Core\Data\Postgres\Sets\Base\BaseSet;

class CrawlerSet extends BaseSet implements ICrawlerSet
{

    private function FillEntityFromData(CrawlerData $data, Crawlers &$entity){
        $entity->setLastCall($data->getLastCall());
        $entity->setAllowedIp(implode(",", $data->getAllowedIPsList()));
    }

    public function Edit(string $id, CrawlerData $data)
    {
        $entity = $this->getManager()
            ->find('AdSearchEngine\Core\Data\Postgres\Entities\Crawlers', $id);
        if (!($entity instanceof Crawlers))
            throw new \Exception("Entity is not of type Crawlers!");
        $this->FillEntityFromData($data, $entity);
    }

    public function RegisterCall(string $id)
    {
        $entity = $this->getManager()
            ->find('AdSearchEngine\Core\Data\Postgres\Entities\Crawlers', $id);
        if (!($entity instanceof Crawlers))
            throw new \Exception("Entity is not of type Crawlers!");
        $entity->setLastCall(new \DateTime());
    }

    public function Exists(string $id): bool
    {
        $entity = $this->getManager()
            ->find('AdSearchEngine\Core\Data\Postgres\Entities\Crawlers', $id);
        return ($entity instanceof Crawlers);
    }
}