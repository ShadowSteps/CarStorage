<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 20:26
 */

namespace Shadows\CarStorage\Crawler\Plugin;


use Shadows\CarStorage\Core\Communication\JobInformation;
use Shadows\CarStorage\Core\Communication\JobRegistration;
use Shadows\CarStorage\Core\Enum\JobType;
use Shadows\CarStorage\Crawler\Index\JobExtractResult;
use Shadows\CarStorage\Crawler\Utils\XPathHelper;

class CarsCrawlerPlugin implements ICrawlerPlugin
{

    public function doHarvestJob(JobInformation $information, \DOMDocument $document): JobRegistration
    {
        $form = $document->getElementById("carsForm");
        $XPath = new \DOMXPath($document);
        $mainTable = XPathHelper::FindElement("table", $form, $XPath);
        $innerTable = XPathHelper::FindElement("table", $mainTable, $XPath);
        $buttonsTable = XPathHelper::FindElementByClass("table", "ver13black", $innerTable, $XPath);
        $resultTable = XPathHelper::FindElementByClass("table", "tableListResults", $innerTable, $XPath);
        $jobRegistration = new JobRegistration($information->getId(), []);
        $nextPageLinks = XPathHelper::FindElementList("a", $buttonsTable, $XPath);
        $carLinks = XPathHelper::FindElementListByClass("a", "ver15black", $resultTable, $XPath);
        for ($i = 0; $i < $nextPageLinks->length; $i++) {
            $pageNode = $nextPageLinks->item($i);
            $url = "http://cars.bg/".$pageNode->attributes->getNamedItem("href")->nodeValue;
            if (strlen($url) > 0){
                $jobRegistration->addNewJob(new JobInformation(
                    "new",
                    $url,
                    JobType::Harvest
                ));
            }
        }
        for ($i = 0; $i < $carLinks->length; $i++) {
            $pageNode = $carLinks->item($i);
            $url = "http://cars.bg/".$pageNode->attributes->getNamedItem("href")->nodeValue;
            if (strlen($url) > 0){
                $jobRegistration->addNewJob(new JobInformation(
                    "new",
                    $url,
                    JobType::Extract
                ));
            }
        }
        return $jobRegistration;
    }

    public function doExtractJob(JobInformation $information, \DOMDocument $document): JobExtractResult
    {
        return null;
    }
}