<?php

use Shadows\CarStorage\Core\Index\SolrClient;
use Shadows\CarStorage\Core\ML\Feature\IndexFeatureExtractor;
use Shadows\CarStorage\Core\ML\IndexRegression;

require_once __DIR__ . "/vendor/autoload.php";

$client = new SolrClient("http://localhost:8983/solr/carstorage/");
$SVR = new IndexRegression();
$SVR->LoadOrTrain($client, __DIR__ . "/model.svr");

$featureExtractor = new IndexFeatureExtractor($client);
$features = $featureExtractor->getFeatureVector();
$documentsCount = 1020;//$client->GetDocumentsCount();
for ($i = 1000; $i < $documentsCount; $i += 10) {
    $documents = [];
    $results = [];
    $rawDocuments = $client->Select("*:*", $i, 10, "id asc");
    foreach ($rawDocuments as $key => $doc) {
        $convertedDoc = [];
        foreach ($features as $feature) {
            /**
             * @var $feature \Shadows\CarStorage\Core\ML\Feature\Feature
             */
            $convertedDoc[] = $doc->{$feature->getName()} / 300000;
        }
        if ($convertedDoc[0] > 1)
            continue;
        $result = $SVR->Predict($convertedDoc);
        echo ($result * 50000) . " <-> " . $doc->price . PHP_EOL;
    }
}