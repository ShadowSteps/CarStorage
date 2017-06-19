<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 5/3/2017
 * Time: 4:00 PM
 */

namespace Shadows\CarStorage\Core\ML\Feature;


use Shadows\CarStorage\Core\Index\DocumentsQueue;
use Shadows\CarStorage\Core\Index\SolrClient;

class IndexFeatureExtractor
{
    private $solrClient;
    public $staticFeatures = [
        "km", "year"
    ];
    public $pointFeature =  "price";

    public function __construct(SolrClient $solrClient)
    {
        $this->solrClient = $solrClient;
    }

    /**
     * @return SolrClient
     */
    public function getSolrClient(): SolrClient
    {
        return $this->solrClient;
    }

    private function getNumericFeaturesCharacteristics(string $feature): NumericFeatureCharacteristics {
        $firstQuartile = $this->getSolrClient()->GetFirstQuartileOfNumericFeature($feature);
        $thirdQuartile = $this->getSolrClient()->GetThirdQuartileOfNumericFeature($feature);
        return new NumericFeatureCharacteristics($firstQuartile, $thirdQuartile);
    }


    public function getFeatureVector(int $minSupportPercent = 12): array {
        $features = [];
        foreach ($this->staticFeatures as $feature) {
            $characteristics = $this->getNumericFeaturesCharacteristics($feature);
            $features[$feature] = new NumericFeature($feature, $characteristics);
        }
        $additionalFeaturesPassed = [];
        $additionalFeatures = [];
        $queue = new DocumentsQueue($this->getSolrClient());
        $minSupportCount = $minSupportPercent / 100 * $queue->getDocCount();
        while (!$queue->isStreamFinished()) {
            $document = $queue->getNextDocument();
            foreach (explode(";", $document->keywords) as $keyword) {
                $keyword = trim($keyword);
                if (is_numeric($keyword))
                    continue;
                if (isset($additionalFeaturesPassed[$keyword]))
                    continue;
                if (mb_strlen($this->getSolrClient()->NormalizeQuery($keyword)) <= 0)
                    continue;
                $additionalFeaturesPassed[$keyword] = true;
                $keyWordCount = $this->getSolrClient()->GetDocumentsCount("keywords:\"$keyword\"");
                if ($keyWordCount <= $minSupportCount || ($queue->getDocCount() - $keyWordCount) <= $minSupportCount)
                    continue;
                $additionalFeatures[$keyword] = new BooleanNumericFeature($keyword);
            }
        }
        return array_values(array_merge($features, $additionalFeatures));
    }
}