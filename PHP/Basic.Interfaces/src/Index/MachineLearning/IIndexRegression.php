<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/15/2017
 * Time: 5:24 PM
 */

namespace AdSearchEngine\Interfaces\Index\MachineLearning;


use AdSearchEngine\Interfaces\Index\MachineLearning\RegressionModel\IIndexRegressionModel;

interface IIndexRegression
{
    public function predictDocumentFeature(IIndexRegressionModel $model, string $predictionFeature, string $documentId);
}