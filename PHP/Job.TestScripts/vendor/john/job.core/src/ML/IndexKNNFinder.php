<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 5/3/2017
 * Time: 6:04 PM
 */

namespace Shadows\CarStorage\Core\ML;


use Phpml\Math\Distance\Euclidean;
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

    public function FindNearest(int $k, string $id): array {
        $document = $this->solrClient->Select("id:".$id, 0, 1);
        $document = $document[0];
        if (!isset($document->cluster))
            return [];
        $cluster = $document->cluster;
        $count = $this->solrClient->GetDocumentsCount("cluster:".$cluster);
        if ($count <= $k) {
            $docs = $this->solrClient->Select("cluster:".$id, 0, 1);
            return $docs;
        } else {
            $features = $this->featureExtractor->getFeatureVector();
            $convertedMainDoc = [];
            foreach ($features as $feature) {
                /**
                 * @var $feature Feature
                 */
                $convertedMainDoc[] = $document->{$feature->getName()}/2000000;
            }
            $nearest = [];
            $maxDistance = PHP_INT_MAX;
            $euclidian = new Euclidean();
            for ($i = 0; $i < $count; $i += $this->step) {
                $rawDocuments = $this->solrClient->Select("cluster:".$cluster, $i, $this->step, "id asc");
                foreach ($rawDocuments as $key => $doc) {
                    if ($doc->id == $id)
                        continue;
                    $convertedDoc = [];
                    foreach ($features as $feature) {
                        /**
                         * @var $feature Feature
                         */
                        $convertedDoc[] = $doc->{$feature->getName()}/2000000;
                    }
                    $distance = $euclidian->distance($convertedMainDoc, $convertedDoc);
                    if (count($nearest) >= $k && $maxDistance <= $distance)
                        continue;
                    if (count($nearest) >= $k)
                        array_pop($nearest);
                    array_push($nearest, ["doc"=>$doc, "distance" => $distance]);
                    usort($nearest, function ($a, $b){
                        if ($a["distance"] == $b["distance"])
                            return 0;
                        return ($a["distance"] < $b["distance"]) ? -1 : 1;
                    });
                    $maxDistance = $nearest[count($nearest) - 1]["distance"];
                }
            }
            foreach ($nearest as $key => $near)
                $nearest[$key] = $near["doc"];
            return $nearest;
        }
    }
}