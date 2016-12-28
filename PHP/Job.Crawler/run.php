<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 19:12
 */
error_reporting(E_ALL ^E_WARNING ^E_NOTICE ^E_DEPRECATED);
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/config.php";

$crawler = new \Shadows\CarStorage\Crawler\Core\Crawler();
while (true) {
    $crawler->Run();
    sleep(1+random_int(0,4));
}