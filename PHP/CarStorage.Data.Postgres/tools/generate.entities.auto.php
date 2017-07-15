<?php
error_reporting(E_ALL ^E_DEPRECATED);
include __DIR__.'/../vendor/autoload.php';
if (count($argv) < 2)
    throw new Exception("Database connection string is not given!");
$connectionString = $argv[1];

//DIRECTORY PARAMETERS
$entitiesDirectory = __DIR__ . '/../src/Entities';

//SCRIPT CODE
function delTree($dir) {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

$connectionParams = @parse_url($connectionString);
if (!$connectionParams)
    throw new Exception("Invalid connection string!");
$params = null;
if (!empty($connectionParams['query'])) {
    parse_str($connectionParams['query'], $params);
    $connectionParams += $params;
}
$classLoader = new \Doctrine\Common\ClassLoader('Entities', __DIR__);
$classLoader->register();
$classLoader = new \Doctrine\Common\ClassLoader('Proxies', __DIR__);
$classLoader->register();
// config
$config = new \Doctrine\ORM\Configuration();
$config->setMetadataDriverImpl($config->newDefaultAnnotationDriver($entitiesDirectory));
$config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache);
$config->setEntityNamespaces(array('CarStorage\Data\Postgres\Entities\\'));
$config->setProxyNamespace('CarStorage\Data\Postgres\Proxies\\');
$config->setProxyDir(__DIR__."/../src/Proxies");
$connectionParams = array(
    'driver' => "pdo_pgsql",
    'host' => $connectionParams["host"],
    'user' => $connectionParams["user"],
    'password' => $connectionParams["pass"],
    'dbname' => substr($connectionParams["path"],1),
    'charset' =>  $connectionParams["charset"]?:"utf8"
);
$em = \Doctrine\ORM\EntityManager::create($connectionParams, $config);
// custom datatypes (not mapped for reverse engineering)
$em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('set', 'string');
$em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
// fetch metadata
$driver = new \Doctrine\ORM\Mapping\Driver\DatabaseDriver(
    $em->getConnection()->getSchemaManager()
);
$driver->setNamespace('CarStorage\Data\Postgres\Entities\\');
$em->getConfiguration()->setMetadataDriverImpl($driver);
$cmf = new \Doctrine\ORM\Tools\DisconnectedClassMetadataFactory($em);
$cmf->setEntityManager($em);
$metadata = $cmf->getAllMetadata();

echo PHP_EOL."GENERATING POSTGRES ENTITIES!".PHP_EOL;
$generator = new Doctrine\ORM\Tools\EntityGenerator();
$generator->setUpdateEntityIfExists(true);
$generator->setGenerateStubMethods(true);
$generator->setGenerateAnnotations(true);
$generator->generate($metadata, __DIR__ . '/../src/Entities');

$files = scandir($entitiesDirectory . "\\CarStorage\\Data\\Postgres\\Entities");
// Identify directories
$source = $entitiesDirectory . "\\CarStorage\\Data\\Postgres\\Entities\\";
$destination = $entitiesDirectory . "\\";
// Cycle through all source files
foreach ($files as $file) {
    if (in_array($file, array(".",".."))) continue;
    copy($source.$file, $destination.$file);
}

delTree($entitiesDirectory . "\\CarStorage");
echo 'Done!'.PHP_EOL;