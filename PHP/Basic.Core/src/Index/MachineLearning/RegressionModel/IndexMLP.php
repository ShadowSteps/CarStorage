<?php

namespace Shadows\CarStorage\Core\ML\RegressionModel;

use AdSearchEngine\Core\Index\IndexDocumentsQueue;
use AdSearchEngine\Core\Index\MachineLearning\RegressionModel\AIndexRegressionModel;
use AdSearchEngine\Core\Index\MachineLearning\Utils\DocumentConvertHelper;
use Phpml\NeuralNetwork\ActivationFunction;
use Phpml\NeuralNetwork\Network\MultilayerPerceptron;

class IndexMLP extends AIndexRegressionModel
{
    /**
     * @var MultilayerPerceptron
     */
    private $perceptron;

    /**
     * @var array
     */
    private $hiddenLayers;

    /**
     * @var float
     */
    private $desiredError;

    /**
     * @var int
     */
    private $maxIterations;

    /**
     * @var ActivationFunction
     */
    private $activationFunction;

    public function __construct(IndexDocumentsQueue $queue, array $features, string $target, array $hiddenLayers = [10], float $desiredError = 0.01, int $maxIterations = 10000, ActivationFunction $activationFunction = null)
    {
        parent::__construct($queue, $features, $target);
        $this->hiddenLayers = $hiddenLayers;
        $this->desiredError = $desiredError;
        $this->maxIterations = $maxIterations;
        $this->activationFunction = $activationFunction;
    }

    public function train(float $trainPercent = 1): array
    {
        $layers = $this->hiddenLayers;
        array_unshift($layers, $this->getFeatureCount() - 1);
        $layers[] = 1;
        $this->perceptron = new MultilayerPerceptron($layers, $this->activationFunction);
        $trainer = new IndexBackpropagation($this->getIndexDocumentsQueue(), $this->getDocumentHelper(), $this->perceptron);
        return $trainer->train($this->getTargetFeature(), $trainPercent, $this->desiredError, $this->maxIterations);
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
                $y = $this->perceptron->setInput($convertedDocument)->getOutput();
            $predicted[] = $y[0];
        }
        return $predicted;
    }
}