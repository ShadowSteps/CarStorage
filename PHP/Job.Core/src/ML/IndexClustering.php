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
use Shadows\CarStorage\Core\Index\SolrClient;
use Shadows\CarStorage\Core\ML\Feature\Feature;
use Shadows\CarStorage\Core\ML\Feature\IndexFeatureExtractor;
use Shadows\CarStorage\Core\Utils\RequestDataMapper;

class IndexClustering
{
    private $solrClient;
    private $featureExtractor;
    private $step = 100;

    public function __construct(string $solrAddress)
    {
        $this->solrClient = new SolrClient($solrAddress);
        $this->featureExtractor = new IndexFeatureExtractor($this->solrClient);
    }

    /**
     * @return SolrClient
     */
    public function getSolrClient(): SolrClient
    {
        return $this->solrClient;
    }

    /**
     * @return IndexFeatureExtractor
     */
    public function getFeatureExtractor(): IndexFeatureExtractor
    {
        return $this->featureExtractor;
    }

    private function generateClusterCentroids(): array{
        $features = $this->getFeatureExtractor()->getFeatureVector();
        $documentsCount = $this->getSolrClient()->GetDocumentsCount();
        $clusterCount = round($documentsCount / 100);
        $documents = [];
        for ($i = 0; $i < $documentsCount; $i += $this->step) {
            $rawDocuments = $this->getSolrClient()->Select("*:*", $i, $this->step, "id asc");
            foreach ($rawDocuments as $key => $doc) {
                $convertedDoc = [];
                foreach ($features as $feature) {
                    /**
                     * @var $feature Feature
                     */
                    $convertedDoc[] = $doc->{$feature->getName()}/2000000;
                }
                $documents[] = $convertedDoc;
            }
        }
        $kMeans = new KMeans($clusterCount);
        $clusters = $kMeans->cluster($documents);
        $centroids = [];
        foreach ($clusters as $cluster) {
            $centroid = [];
            $count = count($cluster);
            foreach ($features as $feature)
                $centroid[$feature->getName()] = 0;
            foreach ($cluster as $dot) {
                foreach ($features as $key => $feature)
                    $centroid[$feature->getName()] += $dot[$key];
            }
            foreach ($features as $feature)
                $centroid[$feature->getName()] = $centroid[$feature->getName()]/$count;
            $centroids[] = $centroid;
        }
        return $centroids;
    }

    private function assignCentroidsToIndex(array $centroids): void {
        $features = $this->getFeatureExtractor()->getFeatureVector();
        $documentsCount = $this->getSolrClient()->GetDocumentsCount();
        $trainingSet = [];
        $trainingResults = [];
        foreach ($centroids as $key => $centroid) {
            $trainingElement = [];
            foreach ($features as $feature) {
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
                foreach ($features as $feature) {
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