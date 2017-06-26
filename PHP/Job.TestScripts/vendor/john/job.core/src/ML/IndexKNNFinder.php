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
use Shadows\CarStorage\Core\ML\Feature\IndexFeatureExtractor;
use Shadows\CarStorage\Utils\DocumentHelper\DocumentHelper;

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

    public function FindNearest(int $k, string $id): array {
        $features = unserialize(file_get_contents(__DIR__."/../../../tmp/features"));
        $documentHelper = new DocumentHelper( $features);
        $document = $this->solrClient->Select("id:".$id, 0, 1);
        $document = $document[0];

        if (!isset($document->cluster))
            return [];
        $cluster = $document->cluster;
        $documentQueue = new DocumentsQueue($this->solrClient,"cluster:$cluster");
        $documentQueue->setStep(400);
        $euclidian = new Euclidean();
        $features = unserialize(file_get_contents(__DIR__."/../../../tmp/features"));
        $convertedFirstDocument = $documentHelper->convertDocumentForClustering($document);
        $distanceDocs = [];
        $count = 0;
        while(!$documentQueue->isStreamFinished()){
            $doc = $documentQueue->getNextDocument();
            $convertedDoc = $documentHelper->convertDocumentForClustering($doc);
            $distance = $euclidian->distance($convertedFirstDocument, $convertedDoc);
            $distanceDocs[$count]["doc"] = $doc;
            $distanceDocs[$count]["distance"] = $distance;
            $count++;
        }
        usort($distanceDocs, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });
        $nearest = array_slice($distanceDocs, 0, $k);
        $result = [];
        foreach($nearest as $num => $document){
            $result[] = $document["doc"];
        }
        return $result;
    }
}