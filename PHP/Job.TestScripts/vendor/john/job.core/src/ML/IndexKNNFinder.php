<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 5/3/2017
 * Time: 6:04 PM
 */

namespace Shadows\CarStorage\Core\ML;


use Phpml\Math\Distance\Euclidean;
use Shadows\CarStorage\Core\Index\DocumentsQueue;
use Shadows\CarStorage\Core\Index\SolrClient;
use Shadows\CarStorage\Core\ML\Feature\Feature;
use Shadows\CarStorage\Core\ML\Feature\IndexFeatureExtractor;

class IndexKNNFinder
{
    private $solrClient;
    private $featureExtractor;
    private $step = 100;

    public function __construct(string $solrAddress)
    {
        $this->solrClient = new SolrClient($solrAddress);
        $this->featureExtractor = new IndexFeatureExtractor($this->solrClient);
    }

    private function convertDocumentForClustering(\stdClass $document,$features):array {
        $convertedDoc = [];
        foreach ($features as $feature) {
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

    public function FindNearest(int $k, string $id): array {
        $document = $this->solrClient->Select("id:".$id, 0, 1);
        $document = $document[0];

        if (!isset($document->cluster))
            return [];
        $cluster = $document->cluster;
        $documentQueue = new DocumentsQueue($this->solrClient,"cluster:$cluster");
        $documentQueue->setStep(400);
        $euclidian = new Euclidean();
        //$nearest = [];
        //$maxDistance = PHP_INT_MAX;
        $features = unserialize(file_get_contents(__DIR__."/../../../tmp/features"));
        $convertedFirstDocument = $this->convertDocumentForClustering($document,$features);
        $distanceDocs = [];
        $count = 0;
        while(!$documentQueue->isStreamFinished()){
            $doc = $documentQueue->getNextDocument();
            $convertedDoc = $this->convertDocumentForClustering($doc,$features);
            $distance = $euclidian->distance($convertedFirstDocument, $convertedDoc);
            $distanceDocs[$count]["doc"] = $doc;
            $distanceDocs[$count]["distance"] = $distance;
            $count++;
        }
        usort($distanceDocs, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });
        $nearest = array_slice($distanceDocs, 0, 5);
        $result = [];
        foreach($nearest as $num => $document){
            $result[] = $document["doc"];
        }
        return $result;
    }
}