<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 5/2/2017
 * Time: 4:14 PM
 */

namespace Shadows\CarStorage\Data\Interfaces\Sets;


use Shadows\CarStorage\Data\Interfaces\DTO\Data\CrawlerData;

interface ICrawlerSet
{
    public function Edit(string $id, CrawlerData $data);
    public function RegisterCall(string $id);
    public function Exists(string $id): bool;
}