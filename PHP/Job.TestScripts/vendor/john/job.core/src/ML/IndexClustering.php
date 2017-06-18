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
    private $step = 300;
    private $docCount;

    public function __construct(string $solrAddress)
    {
        $this->solrClient = new SolrClient($solrAddress);
        $featureExtractor = new IndexFeatureExtractor($this->solrClient);
        $featureExtractor->pointFeature = "";
        $featureExtractor->staticFeatures[] = "price";
        $this->features = $featureExtractor->getFeatureVector();
        $this->docCount = $this->getSolrClient()->GetDocumentsCount();
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

    private function findIntermediateCentroids(array $points, int $clusterCount): array {
        $kMeans = new KMeans($clusterCount);
        $clusters = $kMeans->cluster($points);
        $centroids = [];
        foreach ($clusters as $cluster) {
            $centroid = [];
            $count = count($cluster);
            foreach ($this->features as $feature)
                $centroid[$feature->getName()] = 0;
            if ($count > 0) {
                foreach ($cluster as $dot) {
                    foreach ($this->features as $key => $feature)
                        $centroid[$feature->getName()] += $dot[$key];
                }
                foreach ($this->features as $feature)
                    $centroid[$feature->getName()] = $centroid[$feature->getName()] / $count;
            }
            $centroids[] = [$centroid, $count];
        }
        return $centroids;
    }

    private function generateRandomNumbersArray() {
        $values = range(0, 1, 0.001);
        shuffle($values);
        return array_slice($values, 0, count($this->features));
    }

    private function generateClusterCentroids(): array{
        $progressSpeed = 10;
        $clusterCount = round(/*sqrt($this->docCount)*/ $this->docCount / 1000);
        $facilityCost = 1/($clusterCount * (1 + log($this->docCount)));
        /**
         * @var $facilities Facility[]
         */
        $facilities = [];
        $randomSeed = $this->generateRandomNumbersArray();
        $i = 0;
        $rawDocuments = $this->getSolrClient()->Select("*:*", $i * $this->step, $this->step, "id asc");
        $read = count($rawDocuments);
        $euclidean = new Euclidean();
        $maxFacilities = ($clusterCount * log($this->docCount));
        while ($read > 0) {
            $start = microtime(true);
            $processStart = microtime(true);
            while (count($facilities) <= $maxFacilities && $read > 0) {
                if (count($rawDocuments) == 0)
                {
                    echo "FINISHED $read documents for: ". (microtime(true) - $processStart) . PHP_EOL;
                    $i++;
                    $rawDocuments = $this->getSolrClient()->Select("*:*", $i * $this->step, $this->step, "id asc");
                    $read = count($rawDocuments);
                    $processStart = microtime(true);
                    continue;
                }
                $doc = array_pop($rawDocuments);
                $convertedDoc = $this->convertDocumentForClustering($doc);
                if (count($convertedDoc) <= 0)
                    continue;
                $ANN_Point = ArrayMath::innerProduct($convertedDoc, $randomSeed);
                $min = PHP_INT_MAX;
                $closestFaculty = null;
                $facultyImportKey = 0;
                foreach ($facilities as $key => $facility) {
                    $ANN_facility = $facility->getANNValue();
                    if ($ANN_facility > $ANN_Point) {
                        if ($key == 0) {
                            $min = $euclidean->distance($facility->getPoint(), $convertedDoc);
                            $closestFaculty = $facility;
                        } else {
                            $bottom = $euclidean->distance($facilities[$key - 1]->getPoint(), $convertedDoc);
                            $top = $euclidean->distance($facilities[$key]->getPoint(), $convertedDoc);
                            $min = min($bottom, $top);
                            if ($bottom == $min)
                                $closestFaculty = $facilities[$key - 1];
                            else
                                $closestFaculty = $facility;
                        }
                        $facultyImportKey = $key;
                        break;
                    } else if ($key == count($facilities)) {
                        $min = $euclidean->distance($facility->getPoint(), $convertedDoc);
                        $closestFaculty = $facility;
                        $facultyImportKey = $key + 1;
                    }
                }
                if (min($min/$facilityCost, 1) == 1)
                    array_splice($facilities, $facultyImportKey, 0, [new Facility($convertedDoc, $randomSeed)]);
                else
                    $closestFaculty->addInnerPoint($convertedDoc);
            }
            echo "FACULTY MESUREMENTS: " . (microtime(true) - $start) . PHP_EOL;
            if ($read > 0) {
                $start = microtime(true);
                while (count($facilities) > $maxFacilities) {
                    $facilityCost *= $progressSpeed;
                    $weights = [];
                    foreach ($facilities as $facility){
                        $weights[] = $facility->getInnerPointsCount();
                        $facility->recenterPoint();
                    }
                    usort($facilities, function(Facility $a, Facility $b) {
                        if ($a->getANNValue() == $b->getANNValue())
                            return 0;
                        return $a->getANNValue() < $b->getANNValue() ? -1 : 1;
                    });
                    $newFacilities = [$facilities[0]];
                    foreach ($facilities as $key => $facility) {
                        if ($key == 0)
                            continue;
                        $min = PHP_INT_MAX;
                        $closestFaculty = null;
                        $facultyImportKey = 0;
                        foreach ($newFacilities as $fKey => $newFacility) {
                            $ANN_facility = $newFacility->getANNValue();
                            if ($ANN_facility > $facility->getANNValue()) {
                                if ($fKey == 0) {
                                    $min = $euclidean->distance($newFacility->getPoint(), $facility->getPoint());
                                    $closestFaculty = $newFacility;
                                } else {
                                    $bottom = $euclidean->distance($newFacilities[$key - 1]->getPoint(), $facility->getPoint());
                                    $top = $euclidean->distance($newFacilities[$key]->getPoint(), $facility->getPoint());
                                    $min = min($bottom, $top);
                                    if ($bottom == $min)
                                        $closestFaculty = $newFacilities[$key - 1];
                                    else
                                        $closestFaculty = $newFacility;
                                }
                                $facultyImportKey = $key;
                                break;
                            } else if ($key == count($newFacilities)) {
                                $min = $euclidean->distance($newFacility->getPoint(), $facility->getPoint());
                                $closestFaculty = $newFacility;
                                $facultyImportKey = $key + 1;
                            }
                        }
                        if (min($weights[$key] * $min/$facilityCost, 1) == 1)
                            array_splice($newFacilities, $facultyImportKey, 0, [$facility]);
                        else
                            $closestFaculty->addInnerPoint($facility->getPoint());
                    }
                    $facilities = $newFacilities;
                }
                echo "FACULTY REMESUREMENTS: " . (microtime(true) - $start) . PHP_EOL;
            }
        }
        $points = [];
        foreach ($facilities as $facility)
            $points[] = $facility->getPoint();
        $kMeans = new KMeans($clusterCount);
        $clusters = $kMeans->cluster($points);
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

    private function assignCentroidsToIndex(array $centroids): void {
        $documentsCount = $this->getSolrClient()->GetDocumentsCount();
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
        $classifier = new KNearestNeighbors(1);
        $classifier->train($trainingSet, $trainingResults);
        for ($i = 0; $i < $documentsCount; $i += $this->step) {
            $indexDocuments = [];
            $rawDocuments = $this->getSolrClient()->Select("*:*", $i, $this->step, "id asc");
            foreach ($rawDocuments as $key => $doc) {
                $indexDoc = RequestDataMapper::ConvertStdToJobIndexInformation($doc);
                $convertedDoc = [];
                foreach ($this->features as $feature) {
                    /**
                     * @var $feature Feature
                     */
                    $convertedDoc[] = $doc->{$feature->getName()}/2000000;
                }
                $indexDoc->setCluster($classifier->predict($convertedDoc));
                echo "Document {$doc->url} into cluster: {$indexDoc->getCluster()}". PHP_EOL;
                $indexDocuments[] = $indexDoc;
            }
            $this->getSolrClient()->UpdateDocumentArray($indexDocuments);
        }
    }

    public function beginClustering() {
        $centroids = $this->generateClusterCentroids();
        $this->assignCentroidsToIndex($centroids);
    }


}