<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 5/2/2017
 * Time: 5:09 PM
 */

namespace AppBundle\Utils;


use AdSearchEngine\Interfaces\Utils\ILogger;

class Logger implements ILogger
{
    /**
     * @var \Monolog\Logger
     */
    private static $logger;

    public static function init(\Monolog\Logger $logger){
        self::$logger = $logger;
    }

    public static function warning(string $message, \Throwable $exp = null)
    {
        $context = [
            "IP_ADDRESS" => $_SERVER["REMOTE_ADDR"]
        ];
        if (!is_null($exp))
            $context["exception"] = $exp;
        self::$logger->addWarning($message, $context);
    }

    public static function info(string $message, \Throwable $exp = null)
    {
        $context = [
            "IP_ADDRESS" => $_SERVER["REMOTE_ADDR"]
        ];
        if (!is_null($exp))
            $context["exception"] = $exp;
        self::$logger->addInfo($message, $context);
    }

    public static function debug(string $message, \Throwable $exp = null)
    {
        $context = [
            "IP_ADDRESS" => $_SERVER["REMOTE_ADDR"]
        ];
        if (!is_null($exp))
            $context["exception"] = $exp;
        self::$logger->addDebug($message, $context);
    }

    public static function error(string $message, \Throwable $exp = null)
    {
        $context = [
            "IP_ADDRESS" => $_SERVER["REMOTE_ADDR"]
        ];
        if (!is_null($exp))
            $context["exception"] = $exp;
        self::$logger->addError($message, $context);
    }

    public static function fatal(string $message, \Throwable $exp = null)
    {
        $context = [
            "IP_ADDRESS" => $_SERVER["REMOTE_ADDR"]
        ];
        if (!is_null($exp))
            $context["exception"] = $exp;
        self::$logger->addCritical($message, $context);
    }
}