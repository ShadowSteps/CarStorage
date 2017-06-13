<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 6/13/2017
 * Time: 8:06 PM
 */

namespace Shadows\CarStorage\Core\ML\Feature;


class NumericFeature extends Feature
{
    private $characteristics;

    public function __construct($name, NumericFeatureNormalizationCharacteristics $characteristics)
    {
        parent::__construct($name);
        $this->characteristics = $characteristics;
    }


    public function normalize($value): array
    {
        return [
            $this->getName() => ($value - $this->characteristics->getAverage()) / $this->characteristics->getSigma()
        ];
    }
}