<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/7/2017
 * Time: 3:12 PM
 */
use AdSearchEngine\Core\Index\MachineLearning\FeatureExtraction\FeatureOption\TextFeatureOption;
use AdSearchEngine\Core\Index\MachineLearning\FeatureExtraction\IndexFeatureExtractor;
use AdSearchEngine\Core\Index\ServerClient\SOLRClient;

require_once __DIR__ . "/vendor/autoload.php";

$client = new SOLRClient("http://localhost:8983/solr/carstorage/");
$featureExtractor = new IndexFeatureExtractor($client);
$featureExtractor->addNumericFeature("km");
$featureExtractor->addNumericFeature("price");
$featureExtractor->addNumericFeature("year");
$featureExtractor->addTextFeature(new TextFeatureOption("keywords", function(string $text): array {
    return explode(" ; ", $text);
}, function(string $word): string {
    $word = trim($word);
    $word = str_replace(["\\","\"", "'"], "", $word);
    return $word;
}));
$features = $featureExtractor->getFeatureVector();
$serializedFeatures = serialize($features);
file_put_contents(__DIR__ . "/../tmp/features", $serializedFeatures);