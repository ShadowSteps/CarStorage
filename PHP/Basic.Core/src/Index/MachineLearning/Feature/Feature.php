<?php

namespace AdSearchEngine\Core\Index\MachineLearning\Feature;

abstract class Feature
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public abstract function normalize($value): array;
    public abstract function denormalize($value);
    public abstract function checkValueForExtremes($value): bool;
}