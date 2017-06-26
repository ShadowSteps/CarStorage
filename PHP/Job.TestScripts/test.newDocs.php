<?php
/**
 * Created by PhpStorm.
 * User: mihail
 * Date: 26.6.2017 г.
 * Time: 13:46
 */
require_once __DIR__."/vendor/autoload.php";
use Shadows\CarStorage\Core\Communication\JobInformation;
use Shadows\CarStorage\Crawler\Core\Crawler;
define("CONTROL_API_URL", "http://192.168.50.26:8080/dev/carstorage/api");
define("AUTH_TOKEN", "bb8f7752-033d-4b48-8dc6-a6fbeb73673a");
define("AVAILABLE_PLUGINS", [
    "cars.bg" => "Shadows\\CarStorage\\Crawler\\Plugin\\CarsCrawlerPlugin",
    "olx.bg" => "Shadows\\CarStorage\\Crawler\\Plugin\\OlxCrawlerPlugin"
]);
$jobInfo = new JobInformation("testsaf", "http://www.cars.bg/offer/c2135033", 2);
$crawler = new Crawler();
$crawler->doJob($jobInfo);