<?php
use Phpml\Math\Distance\Euclidean;
use Shadows\CarStorage\Core\Index\SolrClient;
use Shadows\CarStorage\Core\ML\Feature\Feature;
use Shadows\CarStorage\Core\ML\Feature\IndexFeatureExtractor;

require_once __DIR__ . "/vendor/autoload.php";

try {
    $client = new SolrClient("http://localhost:8983/solr/carstorage/");
    $step = 100;
    $featureExtractor = new IndexFeatureExtractor($client);
    $start = microtime(true);
    $features = $featureExtractor->getFeatureVector();
    $time_elapsed_secs = microtime(true) - $start . PHP_EOL;
    echo $time_elapsed_secs;
    foreach ($features as $featureKey => $feature)
        echo $featureKey . PHP_EOL;
    exit;
    $documentsCount = $client->GetDocumentsCount();
    $points = [];
    for ($i = 0; $i < 100; $i += $step) {
        $rawDocuments = $client->Select("*:*", $i, $step, "id desc");
        foreach ($rawDocuments as $key => $doc) {
            $convertedDoc = [];
            foreach ($features as $feature) {
                /**
                 * @var $feature Feature
                 */
               if ($feature->checkValueForExtremes($doc->{$feature->getName()}))
                   continue 2;
               $convertedDoc = array_merge($convertedDoc, $feature->normalize($doc->{$feature->getName()}));
            }
            //$x .= array_values($convertedDoc)[1] . ",";
            //$y .= array_values($convertedDoc)[2] . ",";
        }
    }
} catch (Exception $exception) {
    echo($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
}
//echo rtrim($x, ","). PHP_EOL;
//echo rtrim($y, ","). PHP_EOL;
//echo count (explode(",", $x));
/*$p = 20;
$randSeed = array_rand($points, $p);
$intersect = array_intersect_key($points, array_flip($randSeed));
$randPoints = array_values($intersect);
$randFieldPoints = [];
$array = range(0, 1, 0.00001);
srand ((double)microtime()*1000000);
for($x = 0; $x < $p; $x++)
{
    $i = rand(1, count($array))-1;
    $j = rand(1, count($array))-1;
    $randFieldPoints[] = [$array[$i], $array[$j]];
}
$Euclidian = new Euclidean();
$SumW = 0;
$SumU = 0;
for ($i = 0; $i < $p; $i++) {
    $wi = PHP_INT_MAX;
    $hi = PHP_INT_MAX;
    for ($j = 0; $i < $p; $i++) {
        if ($i == $j)
            continue;
        $distance = $Euclidian->distance($randPoints[$i], $randPoints[$j]);
        if ($distance < $wi)
            $wi = $distance;
    }
    for ($j = 0; $i < $p; $i++) {
        if ($i == $j)
            continue;
        $distance = $Euclidian->distance($randFieldPoints[$i], $randFieldPoints[$j]);
        if ($distance < $hi)
            $hi = $distance;
    }
    $SumW += $wi;
    $SumU += $hi;
}
echo "HOPKINS: " . ($SumW / ($SumW + $SumU));*/