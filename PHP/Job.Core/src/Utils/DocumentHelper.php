<?php

namespace Shadows\CarStorage\Core\Utils;

use Phpml\Classification\KNearestNeighbors;
use Shadows\CarStorage\Core\Index\JobIndexInformation;
use Shadows\CarStorage\Core\ML\Feature\Feature;

/**
 * Created by PhpStorm.
 * User: mihail
 * Date: 26.6.2017 г.
 * Time: 16:05
 */
class DocumentHelper
{
    private $features = [];

    public function __construct($features)
    {
        $this->features = $features;
    }

    /**
     * @return array
     */
    public function getFeatures(): array
    {
        return $this->features;
    }

    public function convertDocumentForClustering(\stdClass $document): array
    {
        $convertedDoc = [];
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
            $convertedDoc = array_merge($convertedDoc, array_values($feature->normalize($value)));
        }
        return $convertedDoc;
    }

    public function defineSets(array $centroids){
        $trainingSet = [];
        $trainingResults = [];
        foreach ($centroids as $key => $centroid) {
            $trainingElement = [];
            foreach ($this->features as $feature) {
                /**
                 * @var $feature Feature
                 */
                $trainingElement[] = $centroid[$feature->getName()];
            }
            $trainingSet[] = $trainingElement;
            $trainingResults[] = $key;
        }
        return [$trainingSet, $trainingResults];
    }

    public function joinDocToCluster(JobIndexInformation $indexDoc)
    {
        $centroids = unserialize(file_get_contents(__DIR__."/../../../tmp/centroids"));
        list($trainingSet, $trainingResults) = $this->defineSets($centroids);
        $classifier = new KNearestNeighbors(1);
        $classifier->train($trainingSet, $trainingResults);
        $doc = (object)$indexDoc->jsonSerializeClean();
        $convertedDoc = $this->convertDocumentForClustering($doc);
        if (count($convertedDoc) > 0) {
            $indexDoc->setCluster($classifier->predict($convertedDoc));
            echo "Document {$doc->url} into cluster: {$indexDoc->getCluster()}" . PHP_EOL;
        } else {
            echo "Document {$doc->url} is extreme." . PHP_EOL;
        }
    }

}