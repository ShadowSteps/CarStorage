<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 19:34
 */

namespace Shadows\CarStorage\Crawler\Core;


use Phpml\Classification\KNearestNeighbors;
use Shadows\CarStorage\Core\Communication\JobInformation;
use Shadows\CarStorage\Core\Enum\JobType;
use Shadows\CarStorage\Core\Index\JobIndexInformation;
use Shadows\CarStorage\Core\ML\Feature\Feature;
use Shadows\CarStorage\Crawler\Utils\NLPHelper;
use Shadows\CarStorage\Utils\DocumentHelper\DocumentHelper;
use Shadows\CarStorage\Utils\Exception\XPathElementNotFoundException;
use Shadows\CarStorage\Crawler\Plugin\ICrawlerPlugin;
use Shadows\CarStorage\Crawler\Scheduler\Client;
use Shadows\CarStorage\Crawler\Utils\Configuration;
use Unirest\Request;

class Crawler
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var NLPHelper
     */
    private $NLP;

    /**
     * Crawler constructor.
     */
    public function __construct()
    {
        $this->client = new Client(Configuration::ControlApiUrl());
        $this->NLP = new NLPHelper();
    }

    public function Run()
    {
        $status = $this->client->GetNextJob();
        if ($status->isStatus() && $status instanceof JobInformation) {
            echo "Job ({$status->getJobType()}): {$status->getUrl()}" . PHP_EOL;
            $this->doJob($status);
        } else {
            echo "No new jobs!" . PHP_EOL;
        }
    }

    //TODO make private again
    private function doJob(JobInformation $information)
    {
        $url = $information->getUrl();
        $parsedUrl = parse_url($url);
        $host = $parsedUrl["host"];
        $host = str_replace("www.", "", $host);
        $availablePlugins = Configuration::AvailablePlugins();
        if (array_key_exists($host, $availablePlugins)) {
            $pluginName = $availablePlugins[$host];
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
                switch ($information->getJobType()) {
                    case JobType::Harvest:
                        $registerInformation = $plugin->doHarvestJob($information, $document);
                        $this->client
                            ->Register($registerInformation);
                        break;
                    case JobType::Extract:
                        $extractResult = $plugin->doExtractJob($information, $document);
                        //$additionalKeywords = $this->getNLP()->ExtractKeywordsFromDescription($extractResult->getJobIndexInformation()->getDescription());
                        //$extractResult->getJobIndexInformation()
                        //    ->addKeywords($additionalKeywords);
                        $this->joinDocToCluster($extractResult->getJobIndexInformation());
                        //TODO uncomment ->index
                        $this->client
                           ->Index($extractResult);
                        break;
                }
            } catch (XPathElementNotFoundException $exp) {
                $this->client->Delete($information->getId());
            }
        }
    }

    private function joinDocToCluster(JobIndexInformation $indexDoc)
    {
        $features = unserialize(file_get_contents(__DIR__."/../../../tmp/features"));
        $documentHelper = new DocumentHelper( $features);
        /**
         * @var $documentHelper DocumentHelper
         */
        $centroids = unserialize(file_get_contents(__DIR__."/../../../tmp/centroids"));
        list($trainingSet, $trainingResults) = $documentHelper->defineSets($centroids);
        $classifier = new KNearestNeighbors(1);
        $classifier->train($trainingSet, $trainingResults);
        $doc = (object)$indexDoc->jsonSerializeClean();
        $convertedDoc = $documentHelper->convertDocumentForClustering($doc);
        if (count($convertedDoc) > 0) {
            $indexDoc->setCluster($classifier->predict($convertedDoc));
            echo "Document {$doc->url} into cluster: {$indexDoc->getCluster()}" . PHP_EOL;
        } else {
            echo "Document {$doc->url} is extreme." . PHP_EOL;
        }
    }

    /**
     * @return NLPHelper
     */
    public function getNLP(): NLPHelper
    {
        return $this->NLP;
    }
}