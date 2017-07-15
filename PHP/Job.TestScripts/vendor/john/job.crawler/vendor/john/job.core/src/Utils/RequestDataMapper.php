<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 15:30
 */

namespace Shadows\CarStorage\Core\Utils;

use Shadows\CarStorage\Core\Communication\ErrorInformation;
use Shadows\CarStorage\Core\Communication\CrawlerExtractJobResultInformation;
use Shadows\CarStorage\Core\Communication\JobInformation;
use Shadows\CarStorage\Core\Communication\CrawlerHarvestJobResultInformation;
use Shadows\CarStorage\Core\Communication\JobStatus;
use CarStorage\Crawler\Index\AutomobileIndexInformation;

class RequestDataMapper
{
    public static function ConvertStdToJobInformation(\stdClass $object) : JobInformation {
        $extractor = new StdClassExtractor($object);
        $data = new JobInformation(
            $extractor->GetString("Id"),
            $extractor->GetString("Url"),
            $extractor->GetInteger("JobType")
        );
        return $data;
    }

    public static function ConvertStdToJobRegistration(\stdClass $object) : CrawlerHarvestJobResultInformation {
        $extractor = new StdClassExtractor($object);
        $data = new CrawlerHarvestJobResultInformation($extractor->GetString("Id"), []);
        $list = $object->NewJobs;
        if (is_array($list)){
            foreach ($list as $subObject) {
                $job = self::ConvertStdToJobInformation($subObject);
                $data->addNewJob($job);
            }
        }
        return $data;
    }

    public static function ConvertStdToJobExtractResult(\stdClass $object) : CrawlerExtractJobResultInformation {
        $registration = self::ConvertStdToJobRegistration($object->jobRegistration);
        $index = self::ConvertStdToJobIndexInformation($object->jobIndexInformation->add->doc);
        $data = new CrawlerExtractJobResultInformation($registration, $index);
        return $data;
    }

    public static function ConvertStdToJobIndexInformation(\stdClass $object) : AutomobileIndexInformation {
        $extractor = new StdClassExtractor($object);
        $data = new AutomobileIndexInformation(
            $extractor->GetString("id"),
            $extractor->GetString("title"),
            $extractor->GetString("description"),
            $extractor->GetString("url"),
            $extractor->GetFloat("price"),
            $extractor->GetString("currency"),
            $extractor->GetDateTime("year", "Y-m-d\\TH:i:s\\Z"),
            $extractor->GetInteger("km"),
            $extractor->GetString("keywords"),
            $extractor->GetInteger("cluster")
        );
        return $data;
    }

    public static function ConvertStdToErrorInformation(\stdClass $object) : ErrorInformation {
        $extractor = new StdClassExtractor($object);
        $data = new ErrorInformation($extractor->GetString("Message"), $extractor->GetString("ExceptionType"));
        return $data;
    }

    public static function ConvertStdToJobStatus(\stdClass $object) : JobStatus {
        $extractor = new StdClassExtractor($object);
        $data = new JobStatus($extractor->GetBoolean("Status"));
        return $data;
    }
}