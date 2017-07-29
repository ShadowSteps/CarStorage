<?php
namespace AdSearchEngine\Interfaces\Communication;

use AdSearchEngine\Interfaces\Communication\Utils\StdClassExtractor;

abstract class JSONCommunicationObject implements \JsonSerializable {
    public function jsonSerialize()
    {
        $reflection = new \ReflectionClass($this);
        $values = [];
        while ($reflection != null) {
            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                $property->setAccessible(true);
                $value = $property->getValue($this);
                if ($value instanceof \JsonSerializable)
                    $value = $value->jsonSerialize();
                else if ($value instanceof \DateTime)
                    $value = $value->format("Y-m-d\\TH:i:s\\Z");
                $values[$property->getName()] = $value;
            }
            $reflection = $reflection->getParentClass();
        }
        return $values;
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