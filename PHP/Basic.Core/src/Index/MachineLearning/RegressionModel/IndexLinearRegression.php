<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/4/2017
 * Time: 9:52 PM
 */

namespace Shadows\CarStorage\Core\ML\RegressionModel;


use AdSearchEngine\Code\Index\IndexDocumentsQueue;
use AdSearchEngine\Core\Index\MachineLearning\RegressionModel\AIndexRegressionModel;
use AdSearchEngine\Core\Index\MachineLearning\Utils\DocumentConvertHelper;

class IndexLinearRegression extends AIndexRegressionModel
{
    private $modelCoefficients = [];
    private $learningRate = 0.1;
    private $momentum = 0.9;
    private $tolerance = 0.00001;
    private $maxIterations = 2000;
    private $batchSize = 50;

    public function __construct(IndexDocumentsQueue $queue, array $features, string $target, float $learningRate = 0.1, float $momentum = 0.9, float $tolerance = 0.00001, int $maxIterations = 2000, int $batchSize = 50)
    {
        parent::__construct($queue, $features, $target);
        $this->learningRate = $learningRate;
        $this->momentum = $momentum;
        $this->tolerance = $tolerance;
        $this->maxIterations = $maxIterations;
        $this->batchSize = $batchSize;
    }


    private function generateRandomCoefficients() {
        $array = range(-0.1, 0.1, 0.001);
        srand ((double)microtime()*1000000);
        for($x = 0; $x < $this->getFeatureCount(); $x++)
        {
            $i = rand(1, count($array))-1;
            $this->modelCoefficients[] = $array[$i];
            array_splice($array, $i, 1);
        }
    }

    private function generateModel(array $vectorOfValues): float{
        $y = 0;
        for ($i = 0; $i < $this->getFeatureCount() - 1; $i++)
            $y += $this->modelCoefficients[$i] * $vectorOfValues[$i];
        $y += $this->modelCoefficients[$this->getFeatureCount() - 1];
        return $y;
    }

    private function runEpoch(int $maxDocument) {
        $this->getIndexDocumentsQueue()->reset();
        $gradient = array_fill(0, $this->getFeatureCount(), 0);
        $batchCount = 0;
        $prevMomentum = array_fill(0, $this->getFeatureCount(), 0);
        $momentumArray = [];
        $docRead = 0;
        while (!$this->getIndexDocumentsQueue()->isStreamFinished() && $docRead < $maxDocument) {
            $document = $this->getIndexDocumentsQueue()->getNextDocument();
            list($convertedDocument, $target) = $this->getDocumentHelper()->convertDocumentForRegression($document, $this->getTargetFeature());
            if (!count($convertedDocument))
                continue;
            $docRead++;
            $y = $this->generateModel($convertedDocument);
            for ($i = 0; $i < $this->getFeatureCount() - 1; $i++)
                $gradient[$i] += -(2 / $maxDocument) * ($convertedDocument[$i]) * ($target[0] - $y);
            $gradient[$this->getFeatureCount() - 1] += -(2 / $maxDocument) * ($target[0] - $y);
            $batchCount++;
            if ($batchCount == $this->batchSize || $this->getIndexDocumentsQueue()->isStreamFinished()) {
                for ($i = 0; $i < $this->getFeatureCount(); $i++) {
                    $momentumArray[$i] = $this->momentum * $prevMomentum[$i] + $this->learningRate * $gradient[$i];
                    $this->modelCoefficients[$i] -= $momentumArray[$i];
                }
                $batchCount = 0;
                $gradient = array_fill(0, $this->getFeatureCount(), 0);
                $prevMomentum = $momentumArray;
                $momentumArray = array_fill(0, $this->getFeatureCount(), 0);
            }
        }
    }

    private function estimateError(int $maxDocument): float {
        $error = 0;
        $this->getIndexDocumentsQueue()->reset();
        $docRead = 0;
        while (!$this->getIndexDocumentsQueue()->isStreamFinished() && $docRead < $maxDocument) {
            $document = $this->getIndexDocumentsQueue()->getNextDocument();
            list($convertedDocument, $target) = $this->getDocumentHelper()->convertDocumentForRegression($document, $this->getTargetFeature());
            if (!count($convertedDocument))
                continue;
            $docRead++;
            $y = $this->generateModel($convertedDocument);
            $error += abs($y - $target[0]);
        }
        return $error / $docRead;
    }

    public function train(float $trainPercent = 1): array
    {
        $this->generateRandomCoefficients();
        $bestCoefficients = [];
        $count = $this->getIndexDocumentsQueue()->getDocCount();
        $errorArray = [];
        $bestError = PHP_INT_MAX;
        $falseIterations = 0;
        $maxDocument = (int)round($count * $trainPercent);
        for ($iter = 0; $iter < $this->maxIterations; $iter++) {
            $this->runEpoch($maxDocument);
            $error = $this->estimateError($maxDocument);
            echo "EPOCH $iter ERROR: ".$error. PHP_EOL;
            if ($error < $bestError) {
                if (abs($bestError - $error) < $this->tolerance)
                    $falseIterations++;
                else
                    $falseIterations = 0;
                $bestError = $error;
                $bestCoefficients = $this->modelCoefficients;
            } else
                $falseIterations++;
            $errorArray[] = $error;
            if ($falseIterations == 5)
                break;
        }
        $this->modelCoefficients = $bestCoefficients;
        return $errorArray;
    }

    public function predict(array $sample): array
    {
        $predicted = [];
        $features = $this->getDocumentHelper()->getFeatures();
        unset($features[$this->getTargetFeature()]);
        $documentHelper = new DocumentConvertHelper($features);
        foreach ($sample as $documentSample) {
            $convertedDocument = $documentHelper->convertDocumentForClustering($documentSample);
            $y = null;
            if (count($convertedDocument))
                $y = $this->generateModel($convertedDocument);
            $predicted[] = $y;
        }
        return $predicted;
    }
}