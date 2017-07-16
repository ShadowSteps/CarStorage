<?php

namespace AdSearchEngine\Core\Index\MachineLearning;

use AdSearchEngine\Core\Index\IndexDocumentsQueue;
use AdSearchEngine\Core\Index\MachineLearning\Feature\Feature;
use AdSearchEngine\Core\Index\MachineLearning\Utils\DocumentConvertHelper;
use AdSearchEngine\Interfaces\Index\ServerClient\IIndexServerClient;
use AdSearchEngine\Interfaces\Index\MachineLearning\IIndexKNNFinder;
use Phpml\Math\Distance\Euclidean;


class IndexKNNFinder implements IIndexKNNFinder
{
    private $indexServerClient;
    /**
     * @var Feature[]
     */
    private $features;
    private $documentHelper;

    public function __construct(IIndexServerClient $client, array $features)
    {
        $this->indexServerClient = $client;
        $this->features = $features;
        $this->documentHelper = new DocumentConvertHelper($this->features);
    }

    public function FindKNearestNeighbours(string $documentId, int $k): array {
        $document = $this->indexServerClient->SelectDocumentById($documentId);
        $clusterFieldName = "cluster";
        if (!isset($document->{$clusterFieldName}))
            return [];
        $cluster = $document->{$clusterFieldName};
        $documentQueue = new IndexDocumentsQueue($this->indexServerClient, "$clusterFieldName:$cluster");
        $documentQueue->setStep(500);
        $euclidean = new Euclidean();
        $convertedFirstDocument = $this->documentHelper->convertDocumentForClustering($document);
        $distanceDocs = [];
        while(!$documentQueue->isStreamFinished()){
            $doc = $documentQueue->getNextDocument();
            if ($doc->id == $documentId)
                continue;
            $convertedDoc = $this->documentHelper->convertDocumentForClustering($doc);
            $distance = $euclidean->distance($convertedFirstDocument, $convertedDoc);
            $distanceDocs[] = [
                "doc" => $doc,
                "distance" => $distance
            ];
            usort($distanceDocs, function($a, $b) {
                return $a['distance'] <=> $b['distance'];
            });
            if (count($distanceDocs) >= $k)
                $distanceDocs = array_slice($distanceDocs, 0, $k);
        }
        $result = [];
        foreach($distanceDocs as $num => $document){
            $result[] = $document["doc"];
        }
        return $result;
    }
}