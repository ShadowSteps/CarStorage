<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 20:26
 */

namespace CarStorage\Crawler\Plugin;


use AdSearchEngine\Core\Crawler\Exception\XPathElementNotFoundException;
use AdSearchEngine\Core\Crawler\Plugin\ICrawlerPlugin;
use AdSearchEngine\Interfaces\Communication\Crawler\Enum\JobType;
use AdSearchEngine\Interfaces\Communication\Crawler\Request\CrawlerExtractJobResultInformation;
use AdSearchEngine\Interfaces\Communication\Crawler\Request\CrawlerHarvestJobResultInformation;
use AdSearchEngine\Interfaces\Communication\Crawler\Response\CrawlerJobInformation;
use CarStorage\Crawler\Index\AutomobileIndexInformation;
use AdSearchEngine\Core\Crawler\Utils\XPathHelper;

class AutoScout24Plugin implements ICrawlerPlugin
{
    public function doHarvestJob(CrawlerJobInformation $information, \DOMDocument $document): CrawlerHarvestJobResultInformation
    {
        $main = $document->getElementById("list");
        $XPath = new \DOMXPath($document);
        $mainGrid = XPathHelper::FindElementByClass("div", "cl-list-items", $main, $XPath);
        $resultTable = XPathHelper::FindElementByClass("ul", "cl-list-elements", $mainGrid, $XPath);
        $carLinks = XPathHelper::FindElementListByClass("div", "gallery-container", $resultTable, $XPath);
        $pagination = XPathHelper::FindElementByClass("ul", "sc-pagination", $mainGrid, $XPath);
        $totalItems = $pagination->attributes->getNamedItem("data-total-items")->nodeValue;
        $itemsPerPage = $pagination->attributes->getNamedItem("data-page-size")->nodeValue;
        $currentPage = (int)$pagination->attributes->getNamedItem("data-current-page")->nodeValue;
        $maxPage = ceil($totalItems / $itemsPerPage);
        $jobRegistration = new CrawlerHarvestJobResultInformation($information->getId(), []);
        for ($i = $currentPage + 1; $i <= $maxPage; $i++){
            $url = $information->getUrl();
            $url = preg_replace("/&page=[0-9]+/", "", $url);
            $url = $url . "&page=$i";
            if (strlen($url) > 0){
                $jobRegistration->addNewJob(new CrawlerJobInformation(
                    "new",
                    $url,
                    JobType::Harvest
                ));
            }
        }
        for ($i = 0; $i < $carLinks->length; $i++) {
            $pageNode = $carLinks->item($i);
            $pageLink = XPathHelper::FindElement("a", $pageNode, $XPath);
            $url = "https://www.autoscout24.com".$pageLink->attributes->getNamedItem("href")->nodeValue;
            if (strlen($url) > 0){
                $jobRegistration->addNewJob(new CrawlerJobInformation(
                    "new",
                    $url,
                    JobType::Extract
                ));
            }
        }
        return $jobRegistration;
    }

    public function doExtractJob(CrawlerJobInformation $information, \DOMDocument $document): CrawlerExtractJobResultInformation
    {
        $form = $document->getElementsByTagName("body")->item(0);
        $XPath = new \DOMXPath($document);
        $mainTable = XPathHelper::FindElementByClass("div", "sc-content-container", $form, $XPath);
        $main = XPathHelper::FindChildElement("main", $mainTable, $XPath);
        $innerTable = XPathHelper::FindElementByClass("div", "cldt-stage-data", $main, $XPath);
        $headerHolder = XPathHelper::FindElementByClass("div", "cldt-headline", $mainTable, $XPath);
        $headerText = trim(XPathHelper::FindElement("h1", $headerHolder, $XPath)->textContent);
        $keywords = [];
        $priceBlock = XPathHelper::FindElement(
            "h2",
            XPathHelper::FindElementByClass(
                "div",
                "sc-grid-row",
                $innerTable,
                $XPath),
            $XPath);
        $priceHolder = str_replace([",",".","-"], "", $priceBlock->textContent);
        $priceElements = explode(" ", $priceHolder);
        $price = trim($priceElements[1]);
        $currency = "euro";
        $highlightsContainer = XPathHelper::FindElementByAttributeValue("div", "data-item-name", "car-details", $main, $XPath);
        $highlightsTables = XPathHelper::FindElementList("dd", $highlightsContainer, $XPath);
        for ($i = 0; $i<$highlightsTables->length; $i++) {
            $td = $highlightsTables->item($i);
            $keywords[] = $td->textContent;
        }
        $dataHolder = XPathHelper::FindElementByClass("div", "cldt-stage-basic-data", $main, $XPath);
        $date = \DateTime::createFromFormat("m/Y",
            XPathHelper::FindElementByClass(
                "span",
                "cldt-stage-primary-keyfact",
                XPathHelper::FindElement(
                    "div",
                    $dataHolder,
                    $XPath,
                    1),
                $XPath)->textContent);
        if (!$date)
            throw new XPathElementNotFoundException("Could not find date in page!");
        $kilometers = trim(str_replace(["km", ","], "", XPathHelper::FindElementByClass(
            "span",
            "cldt-stage-primary-keyfact",
            XPathHelper::FindElement(
                "div",
                $dataHolder,
                $XPath),
            $XPath)->textContent));
        if (mb_strlen($kilometers) <= 0||!is_numeric($kilometers))
            throw new XPathElementNotFoundException("Could not find kilometers in page!");
        $extrasContainer = XPathHelper::FindElementByAttributeValue("div", "data-item-name", "equipments", $main, $XPath);
        $extrasRows = XPathHelper::FindElementList("span", $extrasContainer, $XPath);
        for ($i = 0; $i<$extrasRows->length; $i++) {
            $row = $extrasRows->item($i);
            $keywords[] = trim($row->textContent);
        }
        $descriptionContainer = XPathHelper::FindElementByAttributeValue("div", "data-item-name", "description", $main, $XPath);
        $descriptionRow = XPathHelper::FindElementByAttributeValue("div", "data-type","description", $descriptionContainer, $XPath);
        $description = trim(str_replace(["\n","\t"], "",$descriptionRow->textContent));
        $keywords = array_map("mb_strtolower", $keywords);
        $keywords = array_map("trim", $keywords);
        return new CrawlerExtractJobResultInformation(
            new CrawlerHarvestJobResultInformation($information->getId(), []),
            new AutomobileIndexInformation(str_replace("-", "", $information->getId()), $headerText, $description?:"", $information->getUrl(), floatval($price), mb_strtolower($currency), $date, $kilometers, implode(" ; ",$keywords))
        );
    }
}