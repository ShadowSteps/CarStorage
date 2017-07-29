<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 17:06
 */

namespace AdSearchEngine\Interfaces\Communication\Crawler\Response;

use AdSearchEngine\Interfaces\Communication\JSONCommunicationObject;

class ErrorInformation extends JSONCommunicationObject
{
    private $message;
    private $exceptionType;

    public function __construct(string $message, string $exceptionType)
    {
        $this->message = $message;
        $this->exceptionType = $exceptionType;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getExceptionType(): string
    {
        return $this->exceptionType;
    }
}