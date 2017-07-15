<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/15/2017
 * Time: 3:50 PM
 */

namespace AdSearchEngine\Core\Index\MachineLearning\FeatureExtraction\FeatureOption;


use AdSearchEngine\Core\Index\MachineLearning\Exception\InvalidAnonymousFunctionException;

class TextFeatureOption
{
    private $name;
    private $splitFunction;
    private $normalizeFunction;

    public function __construct(string $name, callable $splitFunction, callable $normalizeFunction)
    {
        $this->name = $name;
        $reflection = new \ReflectionFunction($splitFunction);
        $returnType = $reflection->getReturnType();
        if (is_null($returnType) || $returnType->__toString() != 'array')
            throw new InvalidAnonymousFunctionException("Split function must return array type!");
        $inputParameters = $reflection->getParameters();
        if (count($inputParameters) != 1 || !$inputParameters[0]->hasType() ||
            $inputParameters[0]->getType()->__toString() != 'string')
            throw new InvalidAnonymousFunctionException("Split function must accept only one parameter of type string!");
        $this->splitFunction = $splitFunction;
        $reflection = new \ReflectionFunction($normalizeFunction);
        $returnType = $reflection->getReturnType();
        if (is_null($returnType) || $returnType->__toString() != 'string')
            throw new InvalidAnonymousFunctionException("Split function must return string type!");
        $inputParameters = $reflection->getParameters();
        if (count($inputParameters) != 1 || !$inputParameters[0]->hasType() ||
            $inputParameters[0]->getType()->__toString() != 'string')
            throw new InvalidAnonymousFunctionException("Split function must accept only one parameter of type string!");
        $this->normalizeFunction = $normalizeFunction;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function split(string $textContent): array {
        $function = $this->splitFunction;
        return $function($textContent);
    }

    public function normalize(string $word): string {
        $function = $this->splitFunction;
        return $function($word);
    }
}