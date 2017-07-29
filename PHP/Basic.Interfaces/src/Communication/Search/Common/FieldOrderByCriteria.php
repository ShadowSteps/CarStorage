<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/22/2017
 * Time: 4:16 PM
 */

namespace AdSearchEngine\Interfaces\Communication\Search\Common;


use AdSearchEngine\Interfaces\Communication\JSONCommunicationObject;

class FieldOrderByCriteria extends JSONCommunicationObject
{
    const ORDER_ASC = 'asc';
    const ORDER_DESC = 'desc';

    private $fieldName;
    private $orderType;

    /**
     * FieldOrderByCriteria constructor.
     * @param $fieldName
     * @param $orderType
     */
    public function __construct(string $fieldName, string $orderType)
    {
        $this->fieldName = $fieldName;
        if (!in_array($orderType, [self::ORDER_ASC, self::ORDER_DESC]))
            return;
        $this->orderType = $orderType;
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
    public function getOrderType(): string
    {
        return $this->orderType;
    }
}