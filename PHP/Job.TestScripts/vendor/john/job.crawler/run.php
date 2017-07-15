<?php
use AdSearchEngine\Core\Crawler\Crawler;

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/config.php";

error_reporting(E_ALL ^E_NOTICE ^E_WARNING ^E_DEPRECATED);
$crawler = null;
$i = 0;
while (true) {
    $i++;
    if (is_null($crawler))
        $crawler = new Crawler();
    try {
        $crawler->Run();
    }
    catch (Exception $exp) {
        echo "Exception while doing job: ".$exp->getMessage().PHP_EOL;
    }
    if ($i >= 100) {
        unset($crawler);
        $crawler = null;
    }
    usleep(50000 + random_int(0,350000));
}