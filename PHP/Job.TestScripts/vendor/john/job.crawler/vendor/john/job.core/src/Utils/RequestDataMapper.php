<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 15:30
 */

namespace Shadows\CarStorage\Core\Utils;

use Shadows\CarStorage\Core\Communication\ErrorInformation;
use Shadows\CarStorage\Core\Communication\JobExtractResult;
use Shadows\CarStorage\Core\Communication\JobInformation;
use Shadows\CarStorage\Core\Communication\JobRegistration;
use Shadows\CarStorage\Core\Communication\JobStatus;
use Shadows\CarStorage\Core\Index\JobIndexInformation;

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

    public static function ConvertStdToJobRegistration(\stdClass $object) : JobRegistration {
        $extractor = new StdClassExtractor($object);
        $data = new JobRegistration($extractor->GetString("Id"), []);
        $list = $object->NewJobs;
        if (is_array($list)){
            foreach ($list as $subObject) {
                $job = self::ConvertStdToJobInformation($subObject);
                $data->addNewJob($job);
            }
        }
        return $data;
    }

    public static function ConvertStdToJobExtractResult(\stdClass $object) : JobExtractResult {
        $registration = self::ConvertStdToJobRegistration($object->jobRegistration);
        $index = self::ConvertStdToJobIndexInformation($object->jobIndexInformation->add->doc);
        $data = new JobExtractResult($registration, $index);
        return $data;
    }

    public static function ConvertStdToJobIndexInformation(\stdClass $object) : JobIndexInformation {
        $extractor = new StdClassExtractor($object);
        $data = new JobIndexInformation(
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