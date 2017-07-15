<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 17:06
 */

namespace Shadows\CarStorage\Core\Communication;


class ErrorInformation extends JSONCommunicationObject implements \JsonSerializable
{
    /**
     * @var string
     */
    private $message;
    /**
     * @var string
     */
    private $exceptionType;

    /**
     * ErrorInformation constructor.
     * @param string $message
     * @param string $exceptionType
     */
    public function __construct($message, $exceptionType)
    {
        $this->message = $message;
        $this->exceptionType = $exceptionType;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getExceptionType(): string
    {
        return $this->exceptionType;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
            "Message" => $this->getMessage(),
            "ExceptionType" => $this->getExceptionType()
        ];
    }
}