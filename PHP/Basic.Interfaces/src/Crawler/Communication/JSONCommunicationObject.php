<?php
namespace AdSearchEngine\Interfaces\Crawler\Communication;

use AdSearchEngine\Interfaces\Crawler\Communication\Utils\StdClassExtractor;

abstract class JSONCommunicationObject implements \JsonSerializable {
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
    public static function fromSTD(\stdClass $object) {
        $extractor = new StdClassExtractor($object);
        $reflectionClass = new \ReflectionClass(__CLASS__);
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