<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 5/2/2017
 * Time: 4:27 PM
 */

namespace Shadows\CarStorage\Data\Postgres\Sets;


use Shadows\CarStorage\Data\Interfaces\DTO\Data\CrawlerData;
use Shadows\CarStorage\Data\Interfaces\Sets\ICrawlerSet;
use Shadows\CarStorage\Data\Postgres\Entities\Crawlers;
use Shadows\CarStorage\Data\Postgres\Sets\Base\BaseSet;

class CrawlerSet extends BaseSet implements ICrawlerSet
{

    private function FillEntityFromData(CrawlerData $data, Crawlers &$entity){
        $entity->setLastCall($data->getLastCall());
        $entity->setAllowedIp(implode(",", $data->getAllowedIPsList()));
    }

    public function Edit(string $id, CrawlerData $data)
    {
        $entity = $this->getManager()
            ->find('Shadows\CarStorage\Data\Postgres\Entities\Crawlers', $id);
        if (!($entity instanceof Crawlers))
            throw new \Exception("Entity is not of type Crawlers!");
        $this->FillEntityFromData($data, $entity);
    }

    public function RegisterCall(string $id)
    {
        $entity = $this->getManager()
            ->find('Shadows\CarStorage\Data\Postgres\Entities\Crawlers', $id);
        if (!($entity instanceof Crawlers))
            throw new \Exception("Entity is not of type Crawlers!");
        $entity->setLastCall(new \DateTime());
    }

    public function Exists(string $id): bool
    {
        $entity = $this->getManager()
            ->find('Shadows\CarStorage\Data\Postgres\Entities\Crawlers', $id);
        return ($entity instanceof Crawlers);
    }
}