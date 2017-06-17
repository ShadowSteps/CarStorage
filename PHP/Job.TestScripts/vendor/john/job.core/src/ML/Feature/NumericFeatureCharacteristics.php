<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 6/13/2017
 * Time: 8:27 PM
 */

namespace Shadows\CarStorage\Core\ML\Feature;


class NumericFeatureCharacteristics
{
    private $firstQuartile;
    private $thirdQuartile;
    private $maximum;
    private $minimum;
    private $IQR;

    /**
     * NumericFeatureNormalizationCharacteristics constructor.
     * @param $firstQuartile
     * @param $thirdQuartile
     */
    public function __construct(float $firstQuartile, float $thirdQuartile)
    {
        $this->firstQuartile = $firstQuartile;
        $this->thirdQuartile = $thirdQuartile;
        $this->IQR = $thirdQuartile - $firstQuartile;
        $this->maximum = $thirdQuartile + 2 * $this->IQR;
        $this->minimum = $firstQuartile - 2 * $this->IQR;
    }
    /**
     * @return float
     */
    public function getFirstQuartile(): float
    {
        return $this->firstQuartile;
    }

    /**
     * @return float
     */
    public function getThirdQuartile(): float
    {
        return $this->thirdQuartile;
    }

    /**
     * @return float
     */
    public function getMaximum(): float
    {
        return $this->maximum;
    }

    /**
     * @return float
     */
    public function getMinimum(): float
    {
        return $this->minimum;
    }

    /**
     * @return float
     */
    public function getIQR(): float
    {
        return $this->IQR;
    }



}