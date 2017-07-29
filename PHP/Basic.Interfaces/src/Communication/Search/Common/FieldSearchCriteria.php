<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/22/2017
 * Time: 4:14 PM
 */

namespace AdSearchEngine\Interfaces\Communication\Search\Common;


use AdSearchEngine\Interfaces\Communication\JSONCommunicationObject;

class FieldSearchCriteria extends JSONCommunicationObject
{
    private $fieldName;
    private $fieldWeight = 1;
    private $searchTerm;

    /**
     * FieldSearchCriteria constructor.
     * @param $fieldName
     * @param $fieldWeight
     * @param $searchTerm
     */
    public function __construct(string $fieldName, int $fieldWeight = 1, string $searchTerm = "*")
    {
        $this->fieldName = $fieldName;
        $this->fieldWeight = $fieldWeight;
        $this->searchTerm = $searchTerm;
    }

    /**
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * @return int
     */
    public function getFieldWeight(): int
    {
        return $this->fieldWeight;
    }

    /**
     * @return string
     */
    public function getSearchTerm(): string
    {
        return $this->searchTerm;
    }
}