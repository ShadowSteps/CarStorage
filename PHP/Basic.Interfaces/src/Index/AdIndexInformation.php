<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 19:40
 */

namespace AdSearchEngine\Interfaces\Index;

use JsonSerializable;

abstract class AdIndexInformation implements JsonSerializable
{
    private $id;
    private $cluster = -1;

    public function __construct(string $id, int $nearestSearchCluster = -1)
    {
        $this->id = $id;
        $this->cluster = $nearestSearchCluster;
    }

    function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @param int $cluster
     */
    public function setCluster(int $cluster)
    {
        $this->cluster = $cluster;
    }
}