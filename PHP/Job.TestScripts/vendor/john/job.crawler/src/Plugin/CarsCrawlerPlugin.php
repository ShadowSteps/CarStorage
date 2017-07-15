<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 20:26
 */

namespace Shadows\CarStorage\Crawler\Plugin;


use AdSearchEngine\Core\Crawler\Plugin\ICrawlerPlugin;
use Shadows\CarStorage\Core\Communication\CrawlerExtractJobResultInformation;
use Shadows\CarStorage\Core\Communication\JobInformation;
use Shadows\CarStorage\Core\Communication\CrawlerHarvestJobResultInformation;
use Shadows\CarStorage\Core\Enum\JobType;
use CarStorage\Crawler\Index\AutomobileIndexInformation;
use Shadows\CarStorage\Utils\Exception\XPathElementNotFoundException;
use Shadows\CarStorage\Utils\XPath\XPathHelper;

class CarsCrawlerPlugin implements ICrawlerPlugin
{
    private $dateReplaceStrings = [
        "Януари" => "01",
        "Февруари" => '02',
        "Март" => '03',
        "Април" => '04',
        "Май" => '05',
        "Юни" => '06',
        "Юли" => '07',
        "Август" => '08',
        "Септември" => '09',
        "Октомври" => '10',
        "Ноември" => '11',
        "Декември" => '12'
    ];
    public function doHarvestJob(JobInformation $information, \DOMDocument $document): CrawlerHarvestJobResultInformation
    {
        $form = $document->getElementById("carsForm");
        $XPath = new \DOMXPath($document);
        $mainTable = XPathHelper::FindElement("table", $form, $XPath);
        $innerTable = XPathHelper::FindElement("table", $mainTable, $XPath);
        $buttonsTable = XPathHelper::FindElementByClass("table", "ver13black", $innerTable, $XPath);
        $resultTable = XPathHelper::FindElementByClass("table", "tableListResults", $innerTable, $XPath);
        $jobRegistration = new CrawlerHarvestJobResultInformation($information->getId(), []);
        $nextPageLinks = XPathHelper::FindElementList("a", $buttonsTable, $XPath);
        $carLinks = XPathHelper::FindElementListByClass("a", "ver15black", $resultTable, $XPath);
        for ($i = 0; $i < $nextPageLinks->length; $i++) {
            $pageNode = $nextPageLinks->item($i);
            $url = "http://cars.bg/".$pageNode->attributes->getNamedItem("href")->nodeValue;
            $url = preg_replace("/&+/", "&", $url);
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

    public function doExtractJob(JobInformation $information, \DOMDocument $document): CrawlerExtractJobResultInformation
    {
        $form = $document->getElementsByTagName("body")->item(0);
        $XPath = new \DOMXPath($document);
        $mainTable = XPathHelper::FindChildElement("table", $form, $XPath, 2);
        $mainTd = XPathHelper::FindElement("td", $mainTable, $XPath);
        $innerTable = XPathHelper::FindChildElement("table", $mainTd, $XPath, 2);
        $mainHolderTable = XPathHelper::FindElementByClass("table", "ver13black", $innerTable, $XPath);
        $headerHolder = XPathHelper::FindChildElement("tr", $mainHolderTable, $XPath, 0);
        $headerText = trim($headerHolder->textContent);
        $keywords = [];
        $priceBlock = XPathHelper::FindElement(
            "td",
            XPathHelper::FindElement(
                "table",
                XPathHelper::FindChildElement(
                    "tr",
                    $mainHolderTable,
                    $XPath,
                    1),
                $XPath),
            $XPath,
            1);
        $price = trim(XPathHelper::FindElement("strong", $priceBlock, $XPath)->textContent);
        $currency = trim(str_replace(["\n","\t"], "", str_replace($price, "", $priceBlock->textContent)));
        $price = str_replace(",", "", $price);
        $highlightsContainer = XPathHelper::FindChildElement("tr", $mainHolderTable, $XPath, 2);
        $highlightsInner = XPathHelper::FindElement("table", $highlightsContainer, $XPath);
        $highlightsTables = XPathHelper::FindElementList("table", $highlightsInner, $XPath);
        for ($i = 0; $i<$highlightsTables->length; $i++) {
            $table = $highlightsTables->item($i);
            $highlightsTableRows = XPathHelper::FindElementList("tr", $table, $XPath);
            for ($j = 0; $j < $highlightsTableRows->length; $j++) {
                $row = $highlightsTableRows->item($j);
                $td = XPathHelper::FindElement("td", $row, $XPath, 1);
                $keywords[] = $td->textContent;
            }
        }
        $date = \DateTime::createFromFormat("m Y", str_replace(array_keys($this->dateReplaceStrings), array_values($this->dateReplaceStrings), $keywords[0]));
        if (!$date)
            throw new XPathElementNotFoundException("Could not find date in page!");
        $kilometers = trim(str_replace([",", "км"], "", $keywords[1]));
        if (mb_strlen($kilometers) <= 0||!is_numeric($kilometers))
            throw new XPathElementNotFoundException("Could not find kilometers in page!");
        $extrasContainer = XPathHelper::FindChildElement("tr", $mainHolderTable, $XPath, 3);
        $extrasInner = XPathHelper::FindElement("table", $extrasContainer, $XPath);
        $extrasRows = XPathHelper::FindElementList("tr", $extrasInner, $XPath);
        for ($i = 2; $i<$extrasRows->length; $i++) {
            $row = $extrasRows->item($i);
            $td = XPathHelper::FindElementList("td", $row, $XPath)->item(1);
            $keywordList = $td->textContent;
            foreach (explode(",", $keywordList) as $word)
                $keywords[] = trim($word);
        }
        $descriptionContainer = XPathHelper::FindChildElement("tr", $mainHolderTable, $XPath, 4);
        $descriptionInner = XPathHelper::FindElement("table", $descriptionContainer, $XPath);
        $descriptionRow = XPathHelper::FindElement("tr", $descriptionInner, $XPath, 2);
        $description = trim(str_replace(["\n","\t"], "",$descriptionRow->textContent));
        $keywords = array_map("mb_strtolower", $keywords);
        return new CrawlerExtractJobResultInformation(
            new CrawlerHarvestJobResultInformation($information->getId(), []),
            new AutomobileIndexInformation(str_replace("-", "", $information->getId()), $headerText, $description?:"", $information->getUrl(), floatval($price), mb_strtolower($currency), $date, $kilometers, implode(";",$keywords))
        );
    }
}