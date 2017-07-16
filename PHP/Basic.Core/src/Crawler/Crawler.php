<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 19:34
 */

namespace AdSearchEngine\Core\Crawler;


use AdSearchEngine\Core\Crawler\Exception\XPathElementNotFoundException;
use AdSearchEngine\Core\Index\MachineLearning\Utils\DocumentConvertHelper;
use AdSearchEngine\Interfaces\Crawler\Communication\Enum\JobType;
use AdSearchEngine\Interfaces\Crawler\Communication\Response\CrawlerJobInformation;
use AdSearchEngine\Interfaces\Crawler\ICrawler;
use AdSearchEngine\Interfaces\Index\AdIndexInformation;
use AdSearchEngine\Interfaces\Utils\IAPIClient;
use AdSearchEngine\Core\Crawler\Plugin\ICrawlerPlugin;
use Unirest\Request;

class Crawler implements ICrawler
{
    private $apiClient;
    private $availablePlugins = [];
    private $documentHelper;
    private $clusterCentroids = [];

    public function __construct(IAPIClient $apiClient, array $availablePlugins, array $features, array $clusterCentroids = [])
    {
        $this->apiClient = $apiClient;
        $this->availablePlugins = $availablePlugins;
        $this->documentHelper = new DocumentConvertHelper($features);
        $this->clusterCentroids = $clusterCentroids;
    }

    private function joinDocToCluster(AdIndexInformation $indexDoc): void
    {
        if (!count($this->clusterCentroids))
            return;
        $classifier = $this->documentHelper->getKNNClassifierForCentroids($this->clusterCentroids);
        $doc = $indexDoc->jsonSerialize();
        $convertedDoc = $this->documentHelper->convertDocumentForClustering((object)$doc);
        if (count($convertedDoc) > 0)
            $indexDoc->setCluster($classifier->predict($convertedDoc));
    }

    public function doCrawlerJob(CrawlerJobInformation $jobInformation)
    {
        $url = $jobInformation->getUrl();
        $parsedUrl = parse_url($url);
        $host = $parsedUrl["host"];
        $host = str_replace("www.", "", $host);
        if (array_key_exists($host, $this->availablePlugins)) {
            $pluginName = $this->availablePlugins[$host];
            if (!class_exists($pluginName))
                throw new \Exception("Plugin class does not exist!");
            $plugin = new $pluginName();
            if (!($plugin instanceof ICrawlerPlugin))
                throw new \Exception("Given plugin does not implement ICrawlerPlugin!");
            $response = Request::get($url);
            if ($response->code != 200)
                throw new \Exception("Crawler got job with non existing url!");
            $content = $response->raw_body;
            $document = new \DOMDocument('1.0', "UTF-8");
            $document->loadHTML($content);
            try {
                switch ($jobInformation->getJobType()) {
                    case JobType::Harvest:
                        $registerInformation = $plugin->doHarvestJob($jobInformation, $document);
                        $this->apiClient
                            ->RegisterNewCrawlerJobs($registerInformation);
                        break;
                    case JobType::Extract:
                        $extractResult = $plugin->doExtractJob($jobInformation, $document);
                        $this->joinDocToCluster($extractResult->getAdIndexInformation());
                        $this->apiClient
                            ->AddDocument($extractResult);
                        break;
                }
            } catch (XPathElementNotFoundException $exp) {
                $this->apiClient->DeleteDocument($jobInformation->getId());
            }
        }
    }

    public function doNextJob(): void
    {
        $nextJob = $this->apiClient->GetNextCrawlerJob();
        if ($nextJob->isActive() && $nextJob instanceof CrawlerJobInformation)
            $this->doCrawlerJob($nextJob);
    }
}