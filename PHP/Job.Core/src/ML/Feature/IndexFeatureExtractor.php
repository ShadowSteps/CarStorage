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
    private $staticFeatures = [
        "km"
    ];

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

    private function getNumericFeaturesCharacteristics(string $feature): NumericFeatureNormalizationCharacteristics {
        $average = $this->getSolrClient()->GetAverageOfNumericFeature($feature);
        $sigma = $this->getSolrClient()->GetSigmaDispersionOfNumericFeature($feature);
        return new NumericFeatureNormalizationCharacteristics($sigma, $average);
    }

    public function getFeatureVector(): array {
        $features = [];
        foreach ($this->staticFeatures as $feature) {
            $characteristics = $this->getNumericFeaturesCharacteristics($feature);
            $features[] = new NumericFeature($feature, $characteristics);
        }
        return $features;
    }
}