<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 19:53
 */
define("CONTROL_API_URL", "http://127.0.0.1:8080/dev/carstorage/api");
define("AUTH_TOKEN", "bb8f7752-033d-4b48-8dc6-a6fbeb73673a");
define("AVAILABLE_PLUGINS", [
    "cars.bg" => "Shadows\\CarStorage\\Crawler\\Plugin\\CarsCrawlerPlugin",
    "olx.bg" => "Shadows\\CarStorage\\Crawler\\Plugin\\OlxCrawlerPlugin"
]);