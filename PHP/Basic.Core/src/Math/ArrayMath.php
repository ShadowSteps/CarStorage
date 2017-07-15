<?php

namespace AdSearchEngine\Core\Math;

class ArrayMath
{
    public static function innerProduct(array $a, array $b): float {
        if (count($a) != count($b))
            throw new \Exception("Arrays must be with equal size!");
        $product = 0;
        foreach ($a as $key => $value) {
            $product += $value * $b[$key];
        }
        return $product;
    }
}