<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 6/18/2017
 * Time: 4:24 PM
 */

namespace Shadows\CarStorage\Core\ML\FacilityProblem;


use Shadows\CarStorage\Core\Math\ArrayMath;

class Facility
{
    /**
     * @var array
     */
    private $point;
    /**
     * @var array
     */
    private $newCenterSum;
    /**
     * @var int
     */
    private $innerPointsCount;
    /**
     * @var float
     */
    private $ANN_Value;
    /**
     * @var array
     */
    private $seed;

    public function __construct(array $middlePoints, array $randomSeed)
    {
        $this->point = $middlePoints;
        $this->newCenterSum = $middlePoints;
        $this->innerPointsCount = 1;
        $this->seed = $randomSeed;
        $this->ANN_Value = ArrayMath::innerProduct($this->seed, $this->point);
    }

    public function addInnerPoint(array $point) {
        $final = [];
        $input = [$point, $this->newCenterSum];
        array_walk_recursive($input, function($item, $key) use (&$final){
            $final[$key] = isset($final[$key]) ?  $item + $final[$key] : $item;
        });
        $this->newCenterSum = $final;
        $this->innerPointsCount++;
    }

    public function recenterPoint() {
        $count = $this->innerPointsCount;
        $this->point = array_map(function($value) use ($count) { return $value/$count;}, $this->newCenterSum);
        $this->ANN_Value = ArrayMath::innerProduct($this->seed, $this->point);
    }

    /**
     * @return array
     */
    public function getPoint(): array
    {
        return $this->point;
    }

    /**
     * @return int
     */
    public function getInnerPointsCount(): int
    {
        return $this->innerPointsCount;
    }

    /**
     * @return float
     */
    public function getANNValue(): float
    {
        return $this->ANN_Value;
    }
}