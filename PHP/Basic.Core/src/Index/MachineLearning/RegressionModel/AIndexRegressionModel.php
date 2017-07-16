<?php

namespace AdSearchEngine\Core\Index\MachineLearning\RegressionModel;

use AdSearchEngine\Core\Index\IndexDocumentsQueue;
use AdSearchEngine\Core\Index\MachineLearning\Utils\DocumentConvertHelper;
use AdSearchEngine\Interfaces\Index\MachineLearning\RegressionModel\IIndexRegressionModel;

abstract class AIndexRegressionModel implements IIndexRegressionModel
{
    private $indexDocumentsQueue;
    private $targetFeature;
    private $documentHelper;
    private $featureCount = 0;

    public function __construct(IndexDocumentsQueue $documentsQueue, array $features, string $targetFeature)
    {
        $this->indexDocumentsQueue = $documentsQueue;
        $this->targetFeature = $targetFeature;
        $this->documentHelper = new DocumentConvertHelper($features);
        $this->featureCount = count($features);
    }

    abstract public function train(float $trainPercent = 1): array;
    abstract public function predict(array $sample): array;

    public function testWithMeanSquaredError(float $testSize = 0.33): array {
        $trainSize = 1 - $testSize;
        $startTime = microtime(true);
        $errorCurve = $this->train($trainSize);
        $error = 0;
        $count = 0;
        while (!$this->indexDocumentsQueue->isStreamFinished()) {
            $count++;
            $document = $this->indexDocumentsQueue->getNextDocument();
            list(, $target) = $this->documentHelper->convertDocumentForRegression($document, $this->targetFeature);
            $predicted = $this->predict([$document]);
            $error += abs($predicted[0] - $target[0]);
        }
        $error /= $count;
        return [$errorCurve, $error, microtime(true) - $startTime];
    }

    /**
     * @return IndexDocumentsQueue
     */
    public function getIndexDocumentsQueue(): IndexDocumentsQueue
    {
        return $this->indexDocumentsQueue;
    }

    /**
     * @return DocumentConvertHelper
     */
    public function getDocumentHelper(): DocumentConvertHelper
    {
        return $this->documentHelper;
    }

    /**
     * @return string
     */
    public function getTargetFeature(): string
    {
        return $this->targetFeature;
    }

    /**
     * @return int
     */
    public function getFeatureCount(): int
    {
        return $this->featureCount;
    }
}