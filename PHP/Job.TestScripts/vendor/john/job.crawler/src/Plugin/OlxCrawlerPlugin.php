<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 20:26
 */

namespace Shadows\CarStorage\Crawler\Plugin;


use Shadows\CarStorage\Core\Communication\JobExtractResult;
use Shadows\CarStorage\Core\Communication\JobInformation;
use Shadows\CarStorage\Core\Communication\JobRegistration;
use Shadows\CarStorage\Core\Enum\JobType;
use Shadows\CarStorage\Core\Index\JobIndexInformation;
use Shadows\CarStorage\Utils\Exception\XPathElementNotFoundException;
use Shadows\CarStorage\Utils\XPath\XPathHelper;

class OlxCrawlerPlugin implements ICrawlerPlugin
{

    public function doHarvestJob(JobInformation $information, \DOMDocument $document): JobRegistration
    {
        $section = $document->getElementById("body-container");
        $XPath = new \DOMXPath($document);
        $mainContainer = XPathHelper::FindElementByClass("div","content", $section, $XPath);
        $buttonsTable = XPathHelper::FindElementByClass("div", "pager", $mainContainer, $XPath);
        $resultTable =  $document->getElementById("offers_table");
        $jobRegistration = new JobRegistration($information->getId(), []);
        $nextPageLinks = XPathHelper::FindElementList("a", $buttonsTable, $XPath);
        $carLinks = XPathHelper::FindElementListByClass("a", "link", $resultTable, $XPath);
        for ($i = 0; $i < $nextPageLinks->length; $i++) {
            $pageNode = $nextPageLinks->item($i);
            $url = $pageNode->attributes->getNamedItem("href")->nodeValue;
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
            $url = $pageNode->attributes->getNamedItem("href")->nodeValue;
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
        $offerActionsContainer = $document->getElementById("offeractions");
        $offerContainer = $document->getElementById("offerdescription");
        $XPath = new \DOMXPath($document);
        $title = trim(XPathHelper::FindElement(
            "h1",
            XPathHelper::FindElementByClass(
                "div",
                "offer-titlebox",
                $offerContainer,
                $XPath),
            $XPath)->textContent);
        $descriptionHolder = XPathHelper::FindElementByClass("div", "descriptioncontent", $offerContainer, $XPath);
        $keywords = [];
        $priceBlock = XPathHelper::FindElementByClass(
            "div",
            "price-label",
            $offerActionsContainer,
            $XPath);
        preg_match("/([0-9]+)/", $priceBlock->textContent, $matches);
        $price = $matches[1];
        $currency = trim(str_replace(["\n","\t"], "", str_replace($price, "", $priceBlock->textContent)));
        $price = str_replace(",", "", $price);
        $highlightsContainer = XPathHelper::FindElementByClass("table", "details", $descriptionHolder, $XPath);
        $highlightsTables = XPathHelper::FindElementListByClass("table", "item", $highlightsContainer, $XPath);
        for ($i = 0; $i<$highlightsTables->length; $i++) {
            $table = $highlightsTables->item($i);
            $td = XPathHelper::FindElement("td", $table, $XPath, 0);
            $keywords[] = str_replace(["\n","\t"], "", $td->textContent);
        }
        $date = \DateTime::createFromFormat("Y г.", $keywords[4]);
        if (!$date)
            throw new XPathElementNotFoundException("Could not find date in page!");
        $kilometers = trim(str_replace([",", "км."], "", $keywords[5]));
        if (mb_strlen($kilometers) <= 0||!is_numeric($kilometers))
            throw new XPathElementNotFoundException("Could not find kilometers in page!");
        $description = trim(str_replace(["\n","\t"], "", $document->getElementById("textContent")->textContent));
        $keywords = array_map("mb_strtolower", $keywords);
        return new JobExtractResult(
            new JobRegistration($information->getId(), []),
            new JobIndexInformation(str_replace("-", "", $information->getId()), $title, $description?:"", $information->getUrl(), floatval($price), mb_strtolower($currency), $date, $kilometers, implode(";", $keywords))
        );
    }
}