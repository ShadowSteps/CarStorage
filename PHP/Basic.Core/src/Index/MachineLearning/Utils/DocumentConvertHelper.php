<?php

namespace AdSearchEngine\Core\Index\MachineLearning\Utils;

use AdSearchEngine\Core\Index\MachineLearning\Feature\Feature;
use Phpml\Classification\KNearestNeighbors;


class DocumentConvertHelper
{
    /**
     * @var Feature[]
     */
    private $features = [];
    public function __construct(array $features)
    {
        $this->features = $features;
    }

    /**
     * @return Feature[]
     */
    public function getFeatures(): array
    {
        return $this->features;
    }

    //TODO: FIX IT TO WORK WITH TEXT OPTIONS
    public function convertDocumentForClustering(\stdClass $document): array
    {
        $convertedDoc = [];
        foreach ($this->features as $feature) {
            $value = null;
            if (isset($document->{$feature->getName()}))
                $value = $document->{$feature->getName()};
            else {
                $keywords = explode(";", $document->keywords);
                $keywords = array_map(function($value){ return trim($value); }, $keywords);
                $value = (in_array($feature->getName(), $keywords) ? 1 : 0);
            }
            if ($feature->checkValueForExtremes($value))
                return [];
            $convertedDoc = array_merge($convertedDoc, array_values($feature->normalize($value)));
        }
        return $convertedDoc;
    }

    //TODO: FIX IT TO WORK WITH TEXT OPTIONS
    public function convertDocumentForRegression(\stdClass $document, string $targetFeature): array
    {
        $convertedDoc = [];
        $targetArray = [];
        foreach ($this->features as $feature) {
            /**
             * @var $feature Feature
             */
            $value = null;
            if (property_exists($document, $feature->getName()))
                $value = $document->{$feature->getName()};
            else {
                $keywords = explode(";", $document->keywords);
                $keywords = array_map(function($value){ return trim($value); }, $keywords);
                $value = (in_array($feature->getName(), $keywords) ? 1 : 0);
            }
            if ($feature->checkValueForExtremes($value))
                return [];
            if (strcmp($feature->getName(), $targetFeature) == 0)
                $targetArray = array_merge($targetArray, array_values($feature->normalize($value)));
            else
                $convertedDoc = array_merge($convertedDoc, array_values($feature->normalize($value)));
        }
        return [$convertedDoc, $targetArray];
    }

    public function defineCentroidTrainingSets(array $centroids){
        $trainingSet = [];
        $trainingResults = [];
        foreach ($centroids as $key => $centroid) {
            $trainingElement = [];
            foreach ($this->features as $feature) {
                $trainingElement[] = $centroid[$feature->getName()];
            }
            $trainingSet[] = $trainingElement;
            $trainingResults[] = $key;
        }
        return [$trainingSet, $trainingResults];
    }

    public function getKNNClassifierForCentroids(array $centroids): KNearestNeighbors {
        list($trainingSet, $trainingResults) = $this->defineCentroidTrainingSets($centroids);
        $classifier = new KNearestNeighbors(1);
        $classifier->train($trainingSet, $trainingResults);
        return $classifier;
    }

    public function getClosestCentroidForDocument(\stdClass $document, KNearestNeighbors $nearestNeighbors): int
    {
        $convertedDoc = $this->convertDocumentForClustering($document);
        if (count($convertedDoc) > 0)
            return $nearestNeighbors->predict($convertedDoc);
        else
            return -1;
    }

}