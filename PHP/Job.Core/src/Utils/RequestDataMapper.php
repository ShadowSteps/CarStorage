<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 15:30
 */

namespace Shadows\CarStorage\Core\Utils;

use Shadows\CarStorage\Core\Communication\ErrorInformation;
use Shadows\CarStorage\Core\Communication\JobInformation;
use Shadows\CarStorage\Core\Communication\JobRegistration;
use Shadows\CarStorage\Core\Communication\JobStatus;

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