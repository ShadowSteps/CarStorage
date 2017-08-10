<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 8/5/2017
 * Time: 1:06 PM
 */
error_reporting(E_ALL ^ E_WARNING);
$url = "https://www.mobile.de/?lang=en";
$page_content = file_get_contents($url);
$document = new DOMDocument();
$document->loadHTML($page_content);
$documentXPath = new DOMXPath($document);
$firstLevelUrl = "https://m.mobile.de/svc/r/makes/Car?_jsonp=_loadMakes&_lang=en";
$result = file_get_contents($firstLevelUrl);
$content = str_replace(["_loadMakes(",");"], "", $result);
$elements = json_decode($content, true)["makes"];
$yearsLevel = $document->getElementById("qsfrg")->nextSibling;
$yearElements = $documentXPath->query(".//option", $yearsLevel);
$script = "";
$hashStore = [];
foreach ($elements as $element) {
    $subItemsUrl = "https://m.mobile.de/svc/r/models/{$element["i"]}?_jsonp=_loadModels&_lang=en";
    $subItemsContent = file_get_contents($subItemsUrl);
    $subItemsContent = str_replace(["_loadModels(",");"], "", $subItemsContent);
    $itemsJson = json_decode($subItemsContent, true)["models"];
    foreach ($itemsJson as $item) {
        for ($j = 0; $j < $yearElements->length; $j++) {
            $yearElement = $yearElements->item($j);
            if (!$yearElement->attributes->getNamedItem("value"))
                continue;
            $yearValue = $yearElement->attributes->getNamedItem("value")->nodeValue;
            if ($yearValue == 0)
                continue;
            $searchUrl = "https://suchen.mobile.de/fahrzeuge/search.html?damageUnrepaired=NO_DAMAGE_UNREPAIRED&isSearchRequest=true&makeModelVariant1.makeId={$element["i"]}&makeModelVariant1.modelId={$item["i"]}&maxPowerAsArray=KW&minFirstRegistrationDate=$yearValue-01-01&minPowerAsArray=KW&scopeId=C&lang=en";
            if ($j > 1) {
                $prevYearElement = $yearElements->item($j - 1);
                $prevYearValue = $prevYearElement->attributes->getNamedItem("value")->nodeValue;
                $searchUrl .= "&maxFirstRegistrationDate=$prevYearValue-12-31";
            }

            $hash = hash("sha256", $searchUrl);
            if (!isset($hashStore[$hash])) {
                $script .= 'INSERT INTO jobs ("type", url, hash, locked, crawler_id) values (1, \''.$searchUrl.'\', \''.hash("sha256", $searchUrl).'\', false, \'a28debb0-cce3-46a5-9b76-539e375ad48e\');'. PHP_EOL;
                $hashStore[$hash] = true;
            }
        }

    }
}
file_put_contents(__DIR__ . "/import_mobile_de.sql", $script);
