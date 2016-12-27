<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 19:37
 */

namespace Shadows\CarStorage\Crawler\Plugin;


use Shadows\CarStorage\Core\Communication\JobInformation;
use Shadows\CarStorage\Core\Communication\JobRegistration;
use Shadows\CarStorage\Crawler\Index\JobExtractResult;

interface ICrawlerPlugin
{
    public function doHarvestJob(JobInformation $information, \DOMDocument $document): JobRegistration;
    public function doExtractJob(JobInformation $information, \DOMDocument $document): JobExtractResult;
}