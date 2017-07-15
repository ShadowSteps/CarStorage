<?php

namespace AdSearchEngine\Core\Index\MachineLearning\Feature;

class BooleanNumericFeature extends Feature
{
    public function normalize($value): array
    {
        return [$this->getName() => $value];
    }

    public function checkValueForExtremes($value): bool
    {
        return $value <= 0 && $value >= 1;
    }

    public function denormalize($value)
    {
        return $value;
    }
}