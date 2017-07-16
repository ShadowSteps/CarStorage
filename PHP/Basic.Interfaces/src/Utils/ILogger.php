<?php

namespace AdSearchEngine\Interfaces\Utils;

interface ILogger
{
    public function warning(string $message, \Throwable $exp = null);
    public function info(string $message, \Throwable $exp = null);
    public function debug(string $message, \Throwable $exp = null);
    public function error(string $message, \Throwable $exp = null);
    public function fatal(string $message, \Throwable $exp = null);
}