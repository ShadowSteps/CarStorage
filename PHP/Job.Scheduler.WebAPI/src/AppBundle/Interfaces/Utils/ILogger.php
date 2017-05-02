<?php

namespace AppBundle\Interfaces\Utils;

interface ILogger
{
    public static function warning(string $message, \Throwable $exp = null);
    public static function info(string $message, \Throwable $exp = null);
    public static function debug(string $message, \Throwable $exp = null);
    public static function error(string $message, \Throwable $exp = null);
    public static function fatal(string $message, \Throwable $exp = null);
}