<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/15/2017
 * Time: 8:34 PM
 */

namespace CarStorage\Crawler;


use AdSearchEngine\Core\Crawler\Crawler;
use AdSearchEngine\Core\Utils\APIClient;
use AdSearchEngine\Interfaces\Communication\Crawler\Response\CrawlerJobInformation;
use CarStorage\Crawler\Utils\Configuration;

class Program
{
    public static function main(int $maxTries = -1) {
        $apiClient = new APIClient(Configuration::ControlApiUrl(), Configuration::AuthenticationToken());
        $features = [];
        $featureCachePath = Configuration::FeaturesCacheFile();
        if (file_exists($featureCachePath))
            $features = unserialize(file_get_contents($featureCachePath));
        $centroids = [];
        $centroidCachePath = Configuration::ClusterCentroidsCacheFile();
        if (file_exists($centroidCachePath))
            $centroids = unserialize(file_get_contents($centroidCachePath));
        $crawler = new Crawler($apiClient, Configuration::AvailablePlugins(), $features, $centroids);
        $i = 0;
        while ($maxTries == -1 || $i < $maxTries) {
            $i++;
            try {
                $jobInformation = $crawler->doNextJob();
                if ($jobInformation instanceof CrawlerJobInformation)
                    echo "Job done ({$jobInformation->getJobType()}): ". $jobInformation->getUrl().PHP_EOL;
                else
                    echo "No new jobs available!".PHP_EOL;
            }
            catch (\Exception $exp) {
                echo "Exception while doing job: ".$exp->getMessage().PHP_EOL;
            }
            usleep(300000 + random_int(0,700000));
        }
    }
}