<?php

namespace AdSearchEngine\Core\Utils;

use AdSearchEngine\Interfaces\Utils\ILogger;
use Monolog\Logger;

class MonologLogger implements ILogger
{
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Logger $logger){
        $this->logger = $logger;
    }

    public function warning(string $message, \Throwable $exp = null)
    {
        $context = [
            "IP_ADDRESS" => $_SERVER["REMOTE_ADDR"]
        ];
        if (!is_null($exp))
            $context["exception"] = $exp;
        $this->logger->addWarning($message, $context);
    }

    public function info(string $message, \Throwable $exp = null)
    {
        $context = [
            "IP_ADDRESS" => $_SERVER["REMOTE_ADDR"]
        ];
        if (!is_null($exp))
            $context["exception"] = $exp;
        $this->logger->addInfo($message, $context);
    }

    public function debug(string $message, \Throwable $exp = null)
    {
        $context = [
            "IP_ADDRESS" => $_SERVER["REMOTE_ADDR"]
        ];
        if (!is_null($exp))
            $context["exception"] = $exp;
        $this->logger->addDebug($message, $context);
    }

    public function error(string $message, \Throwable $exp = null)
    {
        $context = [
            "IP_ADDRESS" => $_SERVER["REMOTE_ADDR"]
        ];
        if (!is_null($exp))
            $context["exception"] = $exp;
        $this->logger->addError($message, $context);
    }

    public function fatal(string $message, \Throwable $exp = null)
    {
        $context = [
            "IP_ADDRESS" => $_SERVER["REMOTE_ADDR"]
        ];
        if (!is_null($exp))
            $context["exception"] = $exp;
        $this->logger->addCritical($message, $context);
    }
}