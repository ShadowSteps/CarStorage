<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 15:05
 */

namespace AdSearchEngine\Interfaces\Communication\Crawler\Enum;

use AdSearchEngine\Interfaces\Communication\Crawler\Exception\InvalidJobTypeException;

class JobType
{
    const Harvest = 1;
    const Extract = 2;

    private static $constValuesCache = [];

    public static function isJobType(int $type) {
        if (!count(self::$constValuesCache)) {
            $oClass = new \ReflectionClass(__CLASS__);
            self::$constValuesCache = $oClass->getConstants();
        }
        $isType = in_array($type, self::$constValuesCache);
        if (!$isType)
            throw new InvalidJobTypeException("Invalid job type integer: ".$type);
    }
}