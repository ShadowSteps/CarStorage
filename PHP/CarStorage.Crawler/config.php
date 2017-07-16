<?php
define("ML_FEATURES_CACHE", __DIR__ . "/../tmp/features");
define("ML_CENTROIDS_CACHE", __DIR__ . "/../tmp/centroids");
define("CONTROL_API_URL", "http://127.0.0.1:8080/dev/carstorage/api");
define("AUTH_TOKEN", "bb8f7752-033d-4b48-8dc6-a6fbeb73673a");
define("AVAILABLE_PLUGINS", [
    "cars.bg" => "CarStorage\\Crawler\\Plugin\\CarsCrawlerPlugin",
    "olx.bg" => "CarStorage\\Crawler\\Plugin\\OlxCrawlerPlugin"
]);