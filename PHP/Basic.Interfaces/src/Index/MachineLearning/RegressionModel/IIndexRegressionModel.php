<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/4/2017
 * Time: 9:52 PM
 */

namespace AdSearchEngine\Interfaces\Index\MachineLearning\RegressionModel;

interface IIndexRegressionModel
{
    public function train(float $trainPercent = 1): array;
    public function predict(array $sample): array;
    public function testWithMeanSquaredError(float $testSize = 0.33);
}