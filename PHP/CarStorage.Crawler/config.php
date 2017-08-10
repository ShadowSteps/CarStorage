<?php
define("ML_FEATURES_CACHE", __DIR__ . "/../tmp/features");
define("ML_CENTROIDS_CACHE", __DIR__ . "/../tmp/centroids");
define("CONTROL_API_URL", "http://127.0.0.1:8080/dev/carstorage/api");
define("AUTH_TOKEN", "a28debb0-cce3-46a5-9b76-539e375ad48e");
define("AVAILABLE_PLUGINS", [
    "cars.bg" => "CarStorage\\Crawler\\Plugin\\CarsCrawlerPlugin",
    "autoscout24.com" => "CarStorage\\Crawler\\Plugin\\AutoScout24Plugin",
    "olx.bg" => "CarStorage\\Crawler\\Plugin\\OlxCrawlerPlugin",
    "mobile.de" => "CarStorage\\Crawler\\Plugin\\MobileDePlugin",
    "suchen.mobile.de" => "CarStorage\\Crawler\\Plugin\\MobileDePlugin",
]);