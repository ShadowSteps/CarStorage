<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 5/3/2017
 * Time: 3:29 PM
 */

namespace Shadows\CarStorage\Core\ML;


use Phpml\Classification\KNearestNeighbors;
use Phpml\Clustering\KMeans;
use Phpml\Math\Distance;
use Phpml\Math\Distance\Euclidean;
use Shadows\CarStorage\Core\Index\DocumentsQueue;
use Shadows\CarStorage\Core\Index\SolrClient;
use Shadows\CarStorage\Core\Math\ArrayMath;
use Shadows\CarStorage\Core\ML\FacilityProblem\Facility;
use Shadows\CarStorage\Core\ML\Feature\Feature;
use Shadows\CarStorage\Core\ML\Feature\IndexFeatureExtractor;
use Shadows\CarStorage\Core\Utils\RequestDataMapper;

class IndexClustering
{
    private $solrClient;
    /**
     * @var Feature[]
     */
    private $features = [];

    public function __construct(string $solrAddress)
    {
        $this->solrClient = new SolrClient($solrAddress);
        $featureExtractor = new IndexFeatureExtractor($this->solrClient);
        $featureExtractor->pointFeature = "";
        $featureExtractor->staticFeatures[] = "price";
        $this->features = $featureExtractor->getFeatureVector();
    }

    /**
     * @return SolrClient
     */
    public function getSolrClient(): SolrClient
    {
        return $this->solrClient;
    }

    private function convertDocumentForClustering(\stdClass $document):array {
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

    private function generateRandomNumbersArray() {
        $values = range(0, 1, 0.001);
        shuffle($values);
        return array_slice($values, 0, count($this->features));
    }

    private function ApproximateNN(array $facilities, array $point, float $ANN_Point): array {
        $distance = new Euclidean();
        $min = PHP_INT_MAX;
        $closestFaculty = null;
        $facultyImportKey = 0;
        foreach ($facilities as $key => $facility) {
            $ANN_facility = $facility->getANNValue();
            if ($ANN_facility > $ANN_Point) {
                if ($key == 0) {
                    $min = $distance->distance($facility->getPoint(), $point);
                    $closestFaculty = $facility;
                } else {
                    $bottom = $distance->distance($facilities[$key - 1]->getPoint(), $point);
                    $top = $distance->distance($facilities[$key]->getPoint(), $point);
                    $min = min($bottom, $top);
                    if ($bottom == $min)
                        $closestFaculty = $facilities[$key - 1];
                    else
                        $closestFaculty = $facility;
                }
                $facultyImportKey = $key;
                break;
            } else if ($key == count($facilities)) {
                $min = $distance->distance($facility->getPoint(), $point);
                $closestFaculty = $facility;
                $facultyImportKey = $key + 1;
            }
        }
        return [$min, $closestFaculty, $facultyImportKey];
    }

    private function findBestFacilities(DocumentsQueue $queue, float $facilityCost, int $maxFacilities, float $progressSpeed = 3): array {
        /**
         * @var $facilities Facility[]
         */
        $facilities = [];
        $randomSeed = $this->generateRandomNumbersArray();
        while (!$queue->isStreamFinished()) {
            while (count($facilities) <= $maxFacilities && !$queue->isStreamFinished()) {
                $document = $queue->getNextDocument();
                $convertedDoc = $this->convertDocumentForClustering($document);
                if (count($convertedDoc) <= 0)
                    continue;
                $ANN_Point = ArrayMath::innerProduct($convertedDoc, $randomSeed);
                list($min, $closestFaculty, $facultyImportKey) = $this->ApproximateNN($facilities, $convertedDoc, $ANN_Point);
                if (min($min/ $facilityCost, 1) == 1)
                    array_splice($facilities, $facultyImportKey, 0, [new Facility($convertedDoc, $randomSeed)]);
                else
                    $closestFaculty->addInnerPoint($convertedDoc);
            }
            if (!$queue->isStreamFinished()) {
                while (count($facilities) > $maxFacilities) {
                    $facilityCost *= $progressSpeed;
                    foreach ($facilities as $facility)
                        $facility->recenterPoint();
                    usort($facilities, function(Facility $a, Facility $b) {
                        if ($a->getANNValue() == $b->getANNValue())
                            return 0;
                        return $a->getANNValue() < $b->getANNValue() ? -1 : 1;
                    });
                    $newFacilities = [array_pop($facilities)];
                    foreach ($facilities as $facility) {
                        list($min, $closestFaculty, $facultyImportKey) =
                            $this->ApproximateNN($newFacilities, $facility->getPoint(), $facility->getANNValue());
                        if (min($facility->getInnerPointsCount() * $min / $facilityCost, 1) == 1)
                            array_splice($newFacilities, $facultyImportKey, 0, [$facility]);
                        else
                            $closestFaculty->addInnerPoint($facility->getPoint());
                    }
                    $facilities = $newFacilities;
                }
            }
        }
        return $facilities;
    }

    private function findClusterCentroids(array $clusters): array {
        $centroids = [];
        foreach ($clusters as $cluster) {
            $count = count($cluster);
            if ($count <= 0)
                continue;
            $centroid = [];
            foreach ($this->features as $feature)
                $centroid[$feature->getName()] = 0;
            foreach ($cluster as $dot)
                foreach ($this->features as $key => $feature)
                    $centroid[$feature->getName()] += $dot[$key];
            foreach ($this->features as $feature)
                $centroid[$feature->getName()] = $centroid[$feature->getName()] / $count;
            $centroids[] = $centroid;
        }
        return $centroids;
    }

    private function generateClusterCentroids(): array{
        $start = microtime(true);
        $documentQueue = new DocumentsQueue($this->getSolrClient());
        $documentQueue->setStep(300);
        $clusterCount = round(sqrt($documentQueue->getDocCount()));
        //$clusterCount = round($documentQueue->getDocCount() / 500);
        $facilityCost = 1 / $clusterCount * (1 + log($documentQueue->getDocCount()));
        $maxFacilities = ($clusterCount * log($documentQueue->getDocCount()));
        $facilities = $this->findBestFacilities($documentQueue, $facilityCost, $maxFacilities);
        $points = [];
        foreach ($facilities as $facility)
            $points[] = $facility->getPoint();
        $kMeans = new KMeans($clusterCount);
        $clusters = $kMeans->cluster($points);
        $centroids = $this->findClusterCentroids($clusters);
        echo "CLUSTERING TIME: " . (microtime(true) - $start) . PHP_EOL;
        return $centroids;
    }

    private function defineSets(array $centroids){
        $result = [];
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

    private function assignCentroidsToIndex(array $centroids) {
        list($trainingSet, $trainingResults) = $this->defineSets($centroids);
        $classifier = new KNearestNeighbors(1);
        $classifier->train($trainingSet, $trainingResults);
        $documentQueue = new DocumentsQueue($this->getSolrClient());
        $step = 400;
        $documentQueue->setStep($step);
        $indexDocuments = [];
        while(!$documentQueue->isStreamFinished()){
            $doc = $documentQueue->getNextDocument();
            $indexDoc = RequestDataMapper::ConvertStdToJobIndexInformation($doc);
            $convertedDoc = $this->convertDocumentForClustering($doc);
            if(count($convertedDoc) > 0){
                $indexDoc->setCluster($classifier->predict($convertedDoc));
                echo "Document {$doc->url} into cluster: {$indexDoc->getCluster()}". PHP_EOL;
                $indexDocuments[] = $indexDoc;
                if(count($indexDocuments) == $step) {
                    $this->getSolrClient()->UpdateDocumentArray($indexDocuments);
                    $indexDocuments = [];
                }
            }else{
                echo "Document {$doc->url} is extreme.". PHP_EOL;
            }
        }

    }

    public function beginClustering() {
        $centroids = $this->generateClusterCentroids();
        $this->assignCentroidsToIndex($centroids);
    }


}