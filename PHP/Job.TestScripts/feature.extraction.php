<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/7/2017
 * Time: 3:12 PM
 */
require_once __DIR__ . "/vendor/autoload.php";
use Shadows\CarStorage\Core\Index\SOLRClient;
use Shadows\CarStorage\Core\ML\Feature\IndexTextFeatureExtractor;

$client = new SOLRClient("http://localhost:8983/solr/carstorage/");
$featureExtractor = new IndexTextFeatureExtractor($client);
$featureExtractor->pointFeature = "";
$featureExtractor->staticFeatures[] = "price";
$features = $featureExtractor->getFeatureVector();
$serializedFeatures = serialize($features);
file_put_contents(__DIR__ . "/../tmp/features", $serializedFeatures);