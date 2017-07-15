<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 17:09
 */

namespace Shadows\CarStorage\Core\Communication;


class JobStatus extends JSONCommunicationObject
{
    /**
     * @var bool
     */
    private $status = false;

    /**
     * JobStatus constructor.
     * @param bool $status
     */
    public function __construct($status = false)
    {
        $this->status = $status;
    }

    /**
     * @return bool
     */
    public function isStatus(): bool
    {
        return $this->status;
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
            "Status" => $this->isStatus()
        ];
    }
}