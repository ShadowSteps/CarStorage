<?php

namespace AdSearchEngine\Core\Index\MachineLearning\Feature;

class NumericFeatureCharacteristics
{
    private $firstQuartile;
    private $thirdQuartile;
    private $maximum;
    private $minimum;
    private $IQR;

    public function __construct(float $firstQuartile, float $thirdQuartile)
    {
        $this->firstQuartile = $firstQuartile;
        $this->thirdQuartile = $thirdQuartile;
        $this->IQR = $thirdQuartile - $firstQuartile;
        $this->maximum = $thirdQuartile + 2 * $this->IQR;
        $this->minimum = $firstQuartile - 2 * $this->IQR;
        if ($this->minimum < 0)
            $this->minimum = 0;
    }

    public function getFirstQuartile(): float
    {
        return $this->firstQuartile;
    }

    public function getThirdQuartile(): float
    {
        return $this->thirdQuartile;
    }

    public function getMaximum(): float
    {
        return $this->maximum;
    }

    public function getMinimum(): float
    {
        return $this->minimum;
    }

    public function getIQR(): float
    {
        return $this->IQR;
    }
}