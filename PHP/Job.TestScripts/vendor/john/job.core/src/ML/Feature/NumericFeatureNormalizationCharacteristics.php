<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 6/13/2017
 * Time: 8:27 PM
 */

namespace Shadows\CarStorage\Core\ML\Feature;


class NumericFeatureNormalizationCharacteristics
{
    private $sigma;
    private $average;

    /**
     * NumericFeatureNormalizationCharacteristics constructor.
     * @param $sigma
     * @param $average
     */
    public function __construct(float $sigma, float $average)
    {
        $this->sigma = $sigma;
        $this->average = $average;
    }

    /**
     * @return float
     */
    public function getSigma(): float
    {
        return $this->sigma;
    }

    /**
     * @return float
     */
    public function getAverage(): float
    {
        return $this->average;
    }



}