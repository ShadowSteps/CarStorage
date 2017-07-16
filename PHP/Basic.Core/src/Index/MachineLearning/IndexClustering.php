<?php

namespace AdSearchEngine\Core\Index\MachineLearning;

use AdSearchEngine\Core\Index\IndexDocumentsQueue;
use AdSearchEngine\Core\Index\MachineLearning\FacilityProblem\Facility;
use AdSearchEngine\Core\Index\MachineLearning\Feature\Feature;
use AdSearchEngine\Core\Index\MachineLearning\Utils\DocumentConvertHelper;
use AdSearchEngine\Core\Math\ArrayMath;
use AdSearchEngine\Interfaces\Index\ServerClient\IIndexServerClient;
use AdSearchEngine\Interfaces\Index\MachineLearning\IIndexClustering;
use Phpml\Clustering\KMeans;
use Phpml\Math\Distance\Euclidean;

class IndexClustering implements IIndexClustering
{
    private $indexServerClient;
    /**
     * @var Feature[]
     */
    private $features = [];
    private $documentHelper;

    public function __construct(IIndexServerClient $indexServerClient, array $features)
    {
        $this->indexServerClient = $indexServerClient;
        $this->features = $features;
        $this->documentHelper = new DocumentConvertHelper($this->features);
    }

    public function getIndexServerClient(): IIndexServerClient
    {
        return $this->indexServerClient;
    }

    private function generateRandomNumbersArray(): array
    {
        $values = range(0, 1, 0.001);
        shuffle($values);
        return array_slice($values, 0, count($this->features));
    }

    private function ApproximateNN(array $facilities, array $point, float $ANN_Point): array
    {
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

    private function findBestFacilities(IndexDocumentsQueue $queue, float $facilityCost, int $maxFacilities, float $progressSpeed = 3.0): array
    {
        /**
         * @var $facilities Facility[]
         */
        $facilities = [];
        $randomSeed = $this->generateRandomNumbersArray();
        while (!$queue->isStreamFinished()) {
            while (count($facilities) <= $maxFacilities && !$queue->isStreamFinished()) {
                $document = $queue->getNextDocument();
                $convertedDoc = $this->documentHelper->convertDocumentForClustering($document);
                if (count($convertedDoc) <= 0)
                    continue;
                $ANN_Point = ArrayMath::innerProduct($convertedDoc, $randomSeed);
                list($min, $closestFaculty, $facultyImportKey) = $this->ApproximateNN($facilities, $convertedDoc, $ANN_Point);
                if (min($min / $facilityCost, 1) == 1)
                    array_splice($facilities, $facultyImportKey, 0, [new Facility($convertedDoc, $randomSeed)]);
                else
                    $closestFaculty->addInnerPoint($convertedDoc);
            }
            if (!$queue->isStreamFinished()) {
                while (count($facilities) > $maxFacilities) {
                    $facilityCost *= $progressSpeed;
                    foreach ($facilities as $facility)
                        $facility->recenterPoint();
                    usort($facilities, function (Facility $a, Facility $b) {
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

    private function findClusterCentroids(array $clusters): array
    {
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

    private function generateClusterCentroids(IndexDocumentsQueue $documentQueue): array
    {
        $clusterCount = round(sqrt($documentQueue->getDocCount()));
        $facilityCost = 1 / $clusterCount * (1 + log($documentQueue->getDocCount()));
        $maxFacilities = ($clusterCount * log($documentQueue->getDocCount()));
        $facilities = $this->findBestFacilities($documentQueue, $facilityCost, $maxFacilities);
        $points = [];
        foreach ($facilities as $facility)
            $points[] = $facility->getPoint();
        $kMeans = new KMeans($clusterCount);
        $clusters = $kMeans->cluster($points);
        $centroids = $this->findClusterCentroids($clusters);
        return $centroids;
    }

    private function assignCentroidsToIndex(IndexDocumentsQueue $documentQueue, array $centroids, int $bufferArraySize = 500): void
    {
        $classifier = $this->documentHelper->getKNNClassifierForCentroids($centroids);
        $indexDocuments = [];
        while (!$documentQueue->isStreamFinished()) {
            $doc = $documentQueue->getNextDocument();
            $convertedDoc = $this->documentHelper->convertDocumentForClustering($doc);
            if (count($convertedDoc) > 0) {
                $centroid = $classifier->predict($convertedDoc);
                $indexDocuments[$doc->id] = $centroid;
                if (count($indexDocuments) == $bufferArraySize) {
                    $this->getIndexServerClient()->UpdateDocumentArrayField("cluster", $indexDocuments);
                    $indexDocuments = [];
                }
            }
        }
    }

    public function clusterIndexDocuments(): array
    {
        $queue = new IndexDocumentsQueue($this->getIndexServerClient());
        $centroids = $this->generateClusterCentroids($queue);
        $queue->reset();
        $this->assignCentroidsToIndex($queue, $centroids);
        return $centroids;
    }
}