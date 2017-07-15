<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 14:34
 */

namespace CarStorage\Data\Postgres;


use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use AdSearchEngine\Interfaces\Data\IAdSearchEngineContext;
use AdSearchEngine\Interfaces\Data\Sets\ICrawlerSet;
use AdSearchEngine\Interfaces\Data\Sets\IJobSet;
use CarStorage\Data\Postgres\Sets\CrawlerSet;
use CarStorage\Data\Postgres\Sets\JobSet;

class CarStorageContext implements IAdSearchEngineContext
{
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var IJobSet
     */
    private $jobSet;

    /**
     * @var ICrawlerSet
     */
    private $crawlerSet;

    /**
     * JobSchedulerContext constructor.
     */
    public function __construct(string $host = "localhost", string $username = "postgres", string $password = "", string $dbname = "carstorage_jobscheduler", int $port = 5432, string $charset = "utf8")
    {
        $connectionParams = array(
            'driver' => "pdo_pgsql",
            'host' => $host,
            'user' => $username,
            'password' => $password,
            'dbname' => $dbname,
            "port" => $port,
            "charset" => $charset
        );
        $cache = new ArrayCache();
        $config = new Configuration();
        $config->setMetadataDriverImpl($config->newDefaultAnnotationDriver(__DIR__ . '/Entities'));
        $config->setMetadataCacheImpl($cache);
        $config->setProxyDir(__DIR__ . '/Proxies');
        $config->setProxyNamespace("Shadows\\CarStorage\\Data\\Postgres\\Proxies");
        $this->entityManager = EntityManager::create($connectionParams, $config);
        $this->jobSet = new JobSet($this->entityManager);
        $this->crawlerSet = new CrawlerSet($this->entityManager);
    }

    public function getJobSet(): IJobSet
    {
        return $this->jobSet;
    }

    public function SaveChanges() {
        $this->entityManager
            ->flush();
    }

    public function getCrawlerSet(): ICrawlerSet
    {
        return $this->crawlerSet;
    }
}