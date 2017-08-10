<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 8/5/2017
 * Time: 1:06 PM
 */
error_reporting(E_ALL ^ E_WARNING);
$url = "https://www.autoscout24.com/";
$page_content = file_get_contents($url);
$document = new DOMDocument();
$document->loadHTML($page_content);
$documentXPath = new DOMXPath($document);
$firstLevel = $document->getElementById("home-car-make");
$elements = $documentXPath->query(".//option", $firstLevel);
$yearsLevel = $document->getElementById("home-car-first-registration");
$yearElements = $documentXPath->query(".//option", $yearsLevel);
$script = "";
$hashStore = [];
for ($i = 0; $i < $elements->length; $i++) {
    $element = $elements->item($i);
    $value = $element->attributes->getNamedItem("value")->nodeValue;
    if ($value == 0)
        continue;
    $subItemsUrl = "https://www.autoscout24.com/home/makes/{$value}/models?modelType=C&lang=en-GB";
    $subItemsContent = file_get_contents($subItemsUrl);
    $itemsJson = json_decode($subItemsContent);
    foreach ($itemsJson as $itemInfo) {
        for ($j = 0; $j < $yearElements->length; $j++) {
            $yearElement = $yearElements->item($j);
            $yearValue = $yearElement->attributes->getNamedItem("value")->nodeValue;
            if ($yearValue == 0)
                continue;
            $searchUrl = "https://www.autoscout24.com/results?atype=C&mmvmk0=$value&mmvmd0={$itemInfo->id}&ipc=home%3Asearchbox&ipl=button&zipr=200&mmvco=1&fregfrom=$yearValue";
            if ($j > 1) {
                $prevYearElement = $yearElements->item($j - 1);
                $prevYearValue = $prevYearElement->attributes->getNamedItem("value")->nodeValue;
                $searchUrl .= "&fregto=".$prevYearValue;
            }

            $hash = hash("sha256", $searchUrl);
            if (!isset($hashStore[$hash])) {
                $script .= 'INSERT INTO jobs ("type", url, hash, locked, crawler_id) values (1, \''.$searchUrl.'\', \''.hash("sha256", $searchUrl).'\', false, \'a28debb0-cce3-46a5-9b76-539e375ad48e\');'. PHP_EOL;
                $hashStore[$hash] = true;
            }
        }

    }
}
file_put_contents(__DIR__ . "/import.sql", $script);
