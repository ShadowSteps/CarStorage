<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/29/2017
 * Time: 1:42 PM
 */

namespace AdSearchEngine\Interfaces\Communication\Search\Common;


use AdSearchEngine\Interfaces\Communication\JSONCommunicationObject;

class FieldRangeCriteria extends JSONCommunicationObject
{
    private $fieldName;
    private $fieldMax;
    private $fieldMin;

    /**
     * FieldRangeCriteria constructor.
     * @param $fieldName
     * @param $fieldMax
     * @param $fieldMin
     */
    public function __construct(string $fieldName, string $fieldMax, string $fieldMin)
    {
        $this->fieldName = $fieldName;
        $this->fieldMax = $fieldMax;
        $this->fieldMin = $fieldMin;
    }

    /**
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * @return string
     */
    public function getFieldMax(): string
    {
        return $this->fieldMax;
    }

    /**
     * @return string
     */
    public function getFieldMin(): string
    {
        return $this->fieldMin;
    }

}