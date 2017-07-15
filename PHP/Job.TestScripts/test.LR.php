<?php

use Shadows\CarStorage\Core\Index\DocumentsQueue;
use Shadows\CarStorage\Core\Index\SOLRClient;
use Shadows\CarStorage\Core\ML\Feature\IndexTextFeatureExtractor;
use Shadows\CarStorage\Core\ML\IndexRegression;
use Shadows\CarStorage\Core\ML\RegressionModel\IndexLinearRegression;

require_once __DIR__ . "/vendor/autoload.php";

$client = new SOLRClient("http://localhost:8983/solr/carstorage/");
$featureExtractor = new IndexTextFeatureExtractor($client);
$features = ( file_exists(__DIR__ . "/../tmp/features") ? unserialize(file_get_contents(__DIR__ . "/../tmp/features")) : $featureExtractor->getFeatureVector());
$queue = new DocumentsQueue($client, "*:*", "random_".date("YmdHisuv")." asc");
$queue->setRandom(true);
$queue->setStep(10000);
$regression = new IndexLinearRegression($queue, $features, "price", 0.1, 0.9, 0.00001, 2000);

list($errorCurve, $testError, $learningTime) = $regression->testWithMeanSquaredError();
echo "Test MSE: ". $testError. PHP_EOL;
echo "Learning time: ". $learningTime. PHP_EOL;
$data = [];
foreach ($errorCurve as $epoch => $error) {
    $data[] = [$epoch+1, $error];
}

$fullPath = __DIR__ . "/../tmp/" ;
array_map('unlink', glob( "$fullPath*.tmp.png"));

$temp = tempnam(__DIR__."/../tmp", "pl_");
rename($temp, $temp . ".png");
$temp = $temp . ".png";
$plot = new PHPlot();
$plot->SetDataValues($data);
$plot->SetXTitle("Epoch");
$plot->SetYTitle("Error");
$plot->SetIsInline(true);
$plot->SetOutputFile($temp);
$plot->DrawGraph();
exec($temp);
