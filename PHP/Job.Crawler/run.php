<?php
use Shadows\CarStorage\Crawler\Core\Crawler;

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/config.php";

error_reporting(E_ALL ^E_NOTICE ^E_WARNING ^E_DEPRECATED);
$crawler = new Crawler();
while (true) {
    try {
        $crawler->Run();
    }
    catch (Exception $exp) {
        echo "Exception while doing job: ".$exp->getMessage().PHP_EOL;
    }
    usleep(50000 + random_int(0,350000));
}