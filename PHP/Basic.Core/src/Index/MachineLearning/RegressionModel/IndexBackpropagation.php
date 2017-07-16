<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/5/2017
 * Time: 10:41 PM
 */

namespace Shadows\CarStorage\Core\ML\RegressionModel;


use AdSearchEngine\Core\Index\IndexDocumentsQueue;
use AdSearchEngine\Core\Index\MachineLearning\Utils\DocumentConvertHelper;
use Phpml\NeuralNetwork\Network;
use Phpml\NeuralNetwork\Node\Neuron;
use Phpml\NeuralNetwork\Training\Backpropagation\Sigma;

class IndexBackpropagation
{
    /**
     * @var Network
     */
    private $network;

    /**
     * @var int
     */
    private $theta;

    /**
     * @var array
     */
    private $sigmas;

    private $queue;
    private $documentHelper;

    public function __construct(IndexDocumentsQueue $queue, DocumentConvertHelper $helper, Network $network, int $theta = 1)
    {
        $this->network = $network;
        $this->theta = $theta;
        $this->queue = $queue;
        $this->documentHelper = $helper;
    }

    private function estimateError(int $maxDocument, string $targetFeature): float {
        $error = 0;
        $this->queue->reset();
        $docRead = 0;
        while (!$this->queue->isStreamFinished() && $docRead < $maxDocument) {
            $document = $this->queue->getNextDocument();
            list($convertedDocument, $target) = $this->documentHelper->convertDocumentForRegression($document, $targetFeature);
            if (!count($convertedDocument))
                continue;
            $docRead++;
            $y = $this->network->setInput($convertedDocument)->getOutput();
            $error += abs($y[0] - $target[0]);
        }
        return $error / $docRead;
    }

    public function train(string $target, float $trainSize = 1, float $desiredError = 0.001, int $maxIterations = 10000): array
    {
        $errorArray = [];
        $bestError = PHP_INT_MAX;
        $falseIterations = 0;
        for ($i = 0; $i < $maxIterations; ++$i) {
            $this->queue->reset();
            $maxDocument = (int)round($this->queue->getDocCount() * $trainSize);
            $allCovered = $this->trainSamples($target, $maxDocument, $desiredError);
            $globalError = $this->estimateError($maxDocument, $target);
            echo "Error: ". $globalError. PHP_EOL;
            $errorArray[] = $globalError;
            if ($allCovered) {
                break;
            }
            if ($globalError < $bestError) {
                if (abs($bestError - $globalError) < $desiredError)
                    $falseIterations++;
                else
                    $falseIterations = 0;
                $bestError = $globalError;
            } else
                $falseIterations++;
            if ($falseIterations == 5)
                break;
        }
        return $errorArray;
    }

    private function trainSamples(string $targetFeature, int $maxDocument, float $desiredError): bool
    {
        $resultsWithinError = 0;
        $docRead = 0;
        while (!$this->queue->isStreamFinished() && $docRead < $maxDocument) {
            $document = $this->queue->getNextDocument();
            list($convertedDocument, $target) = $this->documentHelper->convertDocumentForRegression($document, $targetFeature);
            if (!count($convertedDocument))
                continue;
            $docRead++;
            $result = $this->network->setInput($convertedDocument)->getOutput();
            $error = abs($result[0] - $target[0]);
            if ($error < $desiredError) {
                ++$resultsWithinError;
            } else {
                $this->trainSample($convertedDocument, $target);
            }
        }
        echo "RESULTS WITHIN: ".$resultsWithinError.PHP_EOL;
        return $resultsWithinError == $docRead;
    }

    /**
     * @param array $sample
     * @param array $target
     */
    private function trainSample(array $sample, array $target)
    {
        $this->network->setInput($sample)->getOutput();
        $this->sigmas = [];

        $layers = $this->network->getLayers();
        $layersNumber = count($layers);

        for ($i = $layersNumber; $i > 1; --$i) {
            foreach ($layers[$i - 1]->getNodes() as $key => $neuron) {
                if ($neuron instanceof Neuron) {
                    $sigma = $this->getSigma($neuron, $target, $key, $i == $layersNumber);
                    foreach ($neuron->getSynapses() as $synapse) {
                        $synapse->changeWeight($this->theta * $sigma * $synapse->getNode()->getOutput());
                    }
                }
            }
        }
    }

    /**
     * @param Neuron $neuron
     * @param array  $target
     * @param int    $key
     * @param bool   $lastLayer
     *
     * @return float
     */
    private function getSigma(Neuron $neuron, array $target, int $key, bool $lastLayer): float
    {
        $neuronOutput = $neuron->getOutput();
        $sigma = $neuronOutput * (1 - $neuronOutput);

        if ($lastLayer) {
            $sigma *= ($target[$key] - $neuronOutput);
        } else {
            $sigma *= $this->getPrevSigma($neuron);
        }

        $this->sigmas[] = new Sigma($neuron, $sigma);

        return $sigma;
    }

    /**
     * @param Neuron $neuron
     *
     * @return float
     */
    private function getPrevSigma(Neuron $neuron): float
    {
        $sigma = 0.0;

        foreach ($this->sigmas as $neuronSigma) {
            $sigma += $neuronSigma->getSigmaForNeuron($neuron);
        }

        return $sigma;
    }

}