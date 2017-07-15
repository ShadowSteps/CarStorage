<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 19:55
 */

namespace Shadows\CarStorage\Crawler\Utils;


use CarStorage\Crawler\Exception\ConfigParameterNotFoundException;

class ConstConfigHelper
{
    private static function ParameterExists(string $name) {
        if (!defined($name))
            throw new ConfigParameterNotFoundException($name);
    }
    public static function GetStringParameter(string $name): string{
        self::ParameterExists($name);
        $str = constant($name);
        return $str?:"";
    }

    public static function GetStringParameterOrDefault(string $name, string $default): string{
        try {
            return self::GetStringParameter($name);
        }
        catch (ConfigParameterNotFoundException $exp){
            return $default;
        }
    }
    public static function GetBooleanParameter(string $name): bool{
        self::ParameterExists($name);
        $val = constant($name);
        if (!is_bool($val))
            throw new ConfigParameterNotFoundException($name);
        return boolval($val);
    }

    public static function GetBooleanParameterOrDefault(string $name, bool $default): bool{
        try {
            return self::GetBooleanParameter($name);
        }
        catch (ConfigParameterNotFoundException $exp){
            return $default;
        }
    }
    public static function GetIntegerParameter(string $name): int{
        self::ParameterExists($name);
        $strVal = constant($name);
        if (!is_numeric($strVal))
            throw new ConfigParameterNotFoundException($name);
        return intval($strVal);
    }

    public static function GetIntegerParameterOrDefault(string $name, int $default): int{
        try {
            return self::GetIntegerParameter($name);
        }
        catch (ConfigParameterNotFoundException $exp){
            return $default;
        }
    }

    public static function GetArrayParameter(string $name): array{
        self::ParameterExists($name);
        $arrayVal = constant($name);
        if (!is_array($arrayVal))
            throw new ConfigParameterNotFoundException($name);
        return $arrayVal;
    }
}