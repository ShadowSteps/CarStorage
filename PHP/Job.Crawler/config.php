<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 19:53
 */
define("CONTROL_API_URL", "http://np-dev.acstre.com/ccontrol/api");
define("SOLR_API_URL", "http://81.161.246.26:8080/solr/car_storage");
define("AVAILABLE_PLUGINS", [
    "cars.bg" => "Shadows\\CarStorage\\Crawler\\Plugin\\CarsCrawlerPlugin"
]);