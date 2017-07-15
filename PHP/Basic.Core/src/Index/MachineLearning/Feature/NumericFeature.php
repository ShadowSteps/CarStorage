<?php

namespace AdSearchEngine\Core\Index\MachineLearning\Feature;

class NumericFeature extends Feature
{
    private $characteristics;
    public function __construct($name, NumericFeatureCharacteristics $characteristics)
    {
        parent::__construct($name);
        $this->characteristics = $characteristics;
    }


    public function normalize($value): array
    {
        return [
            $this->getName() => (($value - $this->characteristics->getMinimum())/($this->characteristics->getMaximum() - $this->characteristics->getMinimum()))
        ];
    }

    public function checkValueForExtremes($value): bool
    {
        return ($value < $this->characteristics->getMinimum()) || ($value > $this->characteristics->getMaximum());
    }

    public function denormalize($value)
    {
        return ($value * ($this->characteristics->getMaximum() - $this->characteristics->getMinimum())) + $this->characteristics->getMinimum();
    }
}