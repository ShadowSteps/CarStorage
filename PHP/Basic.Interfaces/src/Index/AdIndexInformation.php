<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 19:40
 */

namespace AdSearchEngine\Interfaces\Index;

use AdSearchEngine\Interfaces\Crawler\Communication\Utils\StdClassExtractor;
use JsonSerializable;

abstract class AdIndexInformation implements JsonSerializable
{
    private $id;
    private $cluster = -1;

    public function __construct(string $id, int $nearestSearchCluster = -1)
    {
        $this->id = $id;
        $this->cluster = $nearestSearchCluster;
    }

    function jsonSerialize()
    {
        $reflection = new \ReflectionClass($this);
        $values = [];
        while ($reflection != null) {
            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                $property->setAccessible(true);
                $value = $property->getValue($this);
                if ($value instanceof JsonSerializable)
                    $value = $value->jsonSerialize();
                else if ($value instanceof \DateTime)
                    $value = $value->format("Y-m-d\\TH:i:s\\Z");
                $values[$property->getName()] = $value;
            }
            $reflection = $reflection->getParentClass();
        }
        return $values;
    }

    /**
     * @param int $cluster
     */
    public function setCluster(int $cluster)
    {
        $this->cluster = $cluster;
    }

    public static function fromSTD(\stdClass $object) {
        $extractor = new StdClassExtractor($object);
        $reflectionClass = new \ReflectionClass(get_called_class());
        $constructor = $reflectionClass->getConstructor();
        $inputParameters = $constructor->getParameters();
        $constructorParameters = [];
        foreach ($inputParameters as $key => $inputParameter) {
            $parameterName = $inputParameter->getName();
            $type = $inputParameter->getType();
            if (!($type instanceof \ReflectionType))
                throw new \InvalidArgumentException("Class constructor input parameter[$key] does not have input type!");
            $constructorParameters[] = $extractor->Get($type->__toString(), $parameterName);
        }
        return $reflectionClass->newInstanceArgs($constructorParameters);
    }
}