<?php
use Shadows\CarStorage\Core\ML\IndexClustering;

require_once __DIR__ . "/vendor/autoload.php";

$indexClustering = new IndexClustering("http://localhost:8983/solr/carstorage/");
$indexClustering->beginClustering();