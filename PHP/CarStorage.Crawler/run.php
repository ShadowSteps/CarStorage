<?php
use CarStorage\Crawler\Program;

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/config.php";
error_reporting(E_ALL ^E_WARNING ^E_NOTICE);
Program::main();