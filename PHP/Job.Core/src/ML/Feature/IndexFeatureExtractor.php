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

    public function getFeatureVector(): array {
        return [
            new Feature("km", FeatureType::Number),
            //new Feature("price", FeatureType::Number)
        ];
    }
}