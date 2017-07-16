<?php

use Shadows\CarStorage\Core\Index\DocumentsQueue;
use Shadows\CarStorage\Core\Index\SOLRClient;
use Shadows\CarStorage\Core\ML\Feature\IndexTextFeatureExtractor;
use Shadows\CarStorage\Core\ML\IndexRegression;
use Shadows\CarStorage\Core\ML\RegressionModel\IndexLinearRegression;
use Shadows\CarStorage\Core\ML\RegressionModel\IndexMLP;
use Shadows\CarStorage\Core\ML\RegressionModel\IndexSVR;

require_once __DIR__ . "/vendor/autoload.php";

$client = new SOLRClient("http://localhost:8983/solr/carstorage/");
$featureExtractor = new IndexTextFeatureExtractor($client);
$features = ( file_exists(__DIR__ . "/../tmp/features") ? unserialize(file_get_contents(__DIR__ . "/../tmp/features")) : $featureExtractor->getFeatureVector());
$queue = new DocumentsQueue($client, "*:*", "random_".date("YmdHisuv")." asc");
$queue->setRandom(true);
$queue->setStep(10000);
$regression = new IndexSVR($queue, $features, "price");
$regression->train();
file_put_contents(__DIR__ . "/../tmp/price-model", serialize($regression));