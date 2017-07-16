<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 14:34
 */

namespace AdSearchEngine\Core\Data\Postgres;


use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use AdSearchEngine\Interfaces\Data\IAdSearchEngineContext;
use AdSearchEngine\Interfaces\Data\Sets\ICrawlerSet;
use AdSearchEngine\Interfaces\Data\Sets\IJobSet;
use AdSearchEngine\Core\Data\Postgres\Sets\CrawlerSet;
use AdSearchEngine\Core\Data\Postgres\Sets\JobSet;

class AdSearchEngineContext implements IAdSearchEngineContext
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


    public function __construct(string $host = "localhost", string $username = "postgres", string $password = "", string $dbname = "ad_search_engine", string $port = "5432", string $charset = "utf8")
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
        $config->setProxyNamespace("AdSearchEngine\\Core\\Data\\Postgres\\Proxies");
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