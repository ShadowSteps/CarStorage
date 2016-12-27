<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 15:27
 */

namespace Shadows\CarStorage\Core\Utils;

use InvalidArgumentException;

class StdClassExtractor
{
    /**
     * @var \stdClass
     */
    private $stdObject;

    /**
     * StdClassExtractor constructor.
     * @param \stdClass $stdObject
     */
    public function __construct(\stdClass $stdObject)
    {
        $this->stdObject = $stdObject;
    }

    /**
     * @return \stdClass
     */
    public function getStdObject(): \stdClass
    {
        return $this->stdObject;
    }

    public function GetString(string $paramName): string {
        if (!isset($this->stdObject->{$paramName})||strlen($this->stdObject->{$paramName})<=0)
            throw new InvalidArgumentException("paramName:$paramName");
        return ($this->stdObject->{$paramName})?:"";
    }

    public function GetInteger(string $paramName): int {
        if (!isset($this->stdObject->{$paramName})||!is_numeric($this->stdObject->{$paramName}))
            throw new InvalidArgumentException("paramName:$paramName");
        return intval($this->stdObject->{$paramName});
    }

    public function GetDateTime(string $paramName, string $format = "Y-m-d H:i:s"): \DateTime {
        $dateString = $this->stdObject->{$paramName};
        $dateString = preg_replace('/\\.([0-9]{6})[0-9]+/', '.$1', $dateString);
        if (!isset($dateString)||($Date = \DateTime::createFromFormat($format, $dateString)) === FALSE)
            throw new InvalidArgumentException("paramName:$paramName");
        return $Date;
    }

    public function GetFloat(string $paramName): float {
        if (!isset($this->stdObject->{$paramName})||!is_numeric($this->stdObject->{$paramName}))
            throw new InvalidArgumentException("paramName:$paramName");
        return floatval($this->stdObject->{$paramName});
    }

    public function GetBoolean(string $paramName): bool {
        if (!isset($this->stdObject->{$paramName}))
            throw new InvalidArgumentException("paramName:$paramName");
        $var = $this->stdObject->{$paramName};
        switch ($var) {
            case $var == true:
            case $var == 1:
            case strtolower($var) == 'true':
            case strtolower($var) == 'on':
            case strtolower($var) == 'yes':
            case strtolower($var) == 'y':
                $out = true;
                break;
            default:
                $out = false;
        }
        return $out;
    }
}