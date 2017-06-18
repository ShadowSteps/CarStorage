<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 6/17/2017
 * Time: 4:21 PM
 */

namespace Shadows\CarStorage\Core\ML\Feature;


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
}