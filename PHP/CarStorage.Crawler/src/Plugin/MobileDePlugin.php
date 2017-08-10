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

class MobileDePlugin implements ICrawlerPlugin
{
    public function doHarvestJob(CrawlerJobInformation $information, \DOMDocument $document): CrawlerHarvestJobResultInformation
    {
        $main = $document->getElementsByTagName("body")->item(0);
        $XPath = new \DOMXPath($document);
        $mainGrid = XPathHelper::FindElementByClass("div", "cBox--resultList", $main, $XPath);
        $carLinks = XPathHelper::FindElementListByClass("div", "cBox-body--resultitem", $mainGrid, $XPath);
        $pagination = XPathHelper::FindElementByClass("ul", "pagination", $mainGrid, $XPath);
        $paginationItems = XPathHelper::FindElementListByAttributeValue("span", "data-touch", "link", $pagination, $XPath);
        $jobRegistration = new CrawlerHarvestJobResultInformation($information->getId(), []);
        for ($i = 0; $i < $paginationItems->length; $i++){
            $item = $paginationItems->item($i);
            if (!$item->attributes->getNamedItem("data-href"))
                continue;
            $url = $item->attributes->getNamedItem("data-href")->nodeValue . "&lang=en";
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
            $url = $pageLink->attributes->getNamedItem("href")->nodeValue . "&lang=en";
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
        $mainTable = XPathHelper::FindElementByClass("div", "viewport", $form, $XPath);
        $headerHolder = XPathHelper::FindElementByClass("div", "cBox--vehicle-details", $mainTable, $XPath);
        $headerText = trim(XPathHelper::FindElement("h1", $headerHolder, $XPath)->textContent);
        $keywords = [];
        $priceBlock =
            XPathHelper::FindElementByClass(
                "span",
                "rbt-prime-price",
                $headerHolder,
                $XPath);
        $priceHolder = str_replace([",",".","-"], "", $priceBlock->textContent);
        $price = mb_substr($priceHolder, 3);
        $price = intval($price);
        $currency = "euro";
        $highlightsContainer = XPathHelper::FindElementByClass("div", "cBox-body--technical-data", $mainTable, $XPath);
        $highlightsTables = XPathHelper::FindChildElementListByClass("div", "g-row", $highlightsContainer, $XPath);
        for ($i = 0; $i<$highlightsTables->length; $i++) {
            $td = $highlightsTables->item($i);
            $valueHolder = XPathHelper::FindChildElement("div", $td, $XPath, 1);
            $keywords[] = $valueHolder->textContent;
        }
        $dataHolder = XPathHelper::FindElementByAttributeValue("div", "id", "rbt-firstRegistration-v", $highlightsContainer, $XPath);
        $date = \DateTime::createFromFormat("m/Y",$dataHolder->textContent);
        if (!$date)
            throw new XPathElementNotFoundException("Could not find date in page!");
        $kilometersHolder = XPathHelper::FindElementByAttributeValue("div", "id", "rbt-mileage-v", $highlightsContainer, $XPath);
        $kilometers = (int)trim(str_replace(["km", ","], "", $kilometersHolder->textContent));
        if (mb_strlen($kilometers) <= 0||!is_numeric($kilometers))
            throw new XPathElementNotFoundException("Could not find kilometers in page!");
        $extrasContainer = XPathHelper::FindElementByAttributeValue("div", "id", "rbt-features", $mainTable, $XPath);
        $extrasRows = XPathHelper::FindElementList("p", $extrasContainer, $XPath);
        for ($i = 0; $i<$extrasRows->length; $i++) {
            $row = $extrasRows->item($i);
            $keywords[] = trim($row->textContent);
        }
        $descriptionContainer = XPathHelper::FindElementByClass("div", "cBox-body--vehicledescription", $mainTable, $XPath);
        $descriptionRow = XPathHelper::FindElementByClass("div","description", $descriptionContainer, $XPath);
        $description = trim(str_replace(["\n","\t", "\r"], "",$descriptionRow->textContent));
        $keywords = array_map("mb_strtolower", $keywords);
        $keywords = array_map("trim", $keywords);
        return new CrawlerExtractJobResultInformation(
            new CrawlerHarvestJobResultInformation($information->getId(), []),
            new AutomobileIndexInformation(str_replace("-", "", $information->getId()), $headerText, $description?:"", $information->getUrl(), floatval($price), mb_strtolower($currency), $date, $kilometers, implode(" ; ",$keywords))
        );
    }
}