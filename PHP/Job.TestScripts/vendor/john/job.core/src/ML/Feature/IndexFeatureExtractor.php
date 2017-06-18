<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 5/3/2017
 * Time: 4:00 PM
 */

namespace Shadows\CarStorage\Core\ML\Feature;


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


    public function getFeatureVector(): array {
        $features = [];
        foreach ($this->staticFeatures as $feature) {
            $characteristics = $this->getNumericFeaturesCharacteristics($feature);
            $features[$feature] = new NumericFeature($feature, $characteristics);
        }
        $additionalFeaturesPassed = [];
        $additionalFeatures = [];
        $step = 100;
        $minSupportPercent = 12;
        $documentsCount = $this->getSolrClient()->GetDocumentsCount();
        $minSupportCount = $minSupportPercent / 100 * $documentsCount;
        for ($i = 0; $i < 2000; $i += $step) {
            $rawDocuments = $this->getSolrClient()->Select("*:*", $i, $step, "id asc");
            foreach ($rawDocuments as $key => $doc) {
                foreach (explode(";", $doc->keywords) as $keyword) {
                    $keyword = trim($keyword);
                    if (is_numeric($keyword))
                        continue;
                    if (isset($additionalFeaturesPassed[$keyword]))
                        continue;
                    if (mb_strlen($this->getSolrClient()->NormalizeQuery($keyword)) <= 0)
                        continue;
                    $additionalFeaturesPassed[$keyword] = true;
                    $keyWordCount = $this->getSolrClient()->GetDocumentsCount("keywords:\"$keyword\"");
                    if ($keyWordCount <= $minSupportCount || ($documentsCount - $keyWordCount) <= $minSupportCount)
                        continue;
                    $additionalFeatures[$keyword] = new BooleanNumericFeature($keyword);
                }
            }
        }
        return array_values(array_merge($features, $additionalFeatures));
    }
}