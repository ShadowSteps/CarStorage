<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 19:13
 */

namespace Shadows\CarStorage\Crawler\Scheduler;


use Shadows\CarStorage\Core\Communication\CrawlerExtractJobResultInformation;
use Shadows\CarStorage\Core\Communication\CrawlerHarvestJobResultInformation;
use Shadows\CarStorage\Core\Communication\JobStatus;
use Shadows\CarStorage\Core\Utils\RequestDataMapper;
use Shadows\CarStorage\Crawler\Utils\Configuration;
use Unirest\Request;
use Unirest\Response;

class Client
{
    /**
     * @var string
     */
    private $controlBaseApiUrl;

    /**
     * Client constructor.
     * @param string $controlBaseApiUrl
     */
    public function __construct(string $controlBaseApiUrl)
    {
        $this->controlBaseApiUrl = $controlBaseApiUrl;
    }

    private function ValidateResponse(Response $response) : \stdClass{
        $content = $response->raw_body;
        if (strlen($content) <= 0)
            throw new \Exception("Response is empty!");
        $std = json_decode($content);
        if (!$std)
            throw new \Exception("Response is not json format!");
        if ($response->code != 200)
        {
            try {
                $error = RequestDataMapper::ConvertStdToErrorInformation($std);
                $message = "Error receive from server({$error->getExceptionType()}): {$error->getMessage()}!";
            } catch (\Exception $exp) {
                $message = "Could not parse response from server!";
            }
            throw new \Exception($message);
        }
        return $std;
    }

    public function GetNextJob(): JobStatus {
        $response = Request::get(
            $this->controlBaseApiUrl . "/job/next",
            ["AUTH_TOKEN" => Configuration::AuthenticationToken()]
        );
        $std = $this->ValidateResponse($response);
        $status = RequestDataMapper::ConvertStdToJobStatus($std);
        if (!$status->isStatus())
            return $status;
        return RequestDataMapper::ConvertStdToJobInformation($std);
    }

    public function Register(CrawlerHarvestJobResultInformation $registration): JobStatus {
        $response = Request::post(
            $this->controlBaseApiUrl . "/job/register",
            ["AUTH_TOKEN" => Configuration::AuthenticationToken()],
            json_encode($registration)
        );
        $std = $this->ValidateResponse($response);
        return RequestDataMapper::ConvertStdToJobStatus($std);
    }

    public function Index(CrawlerExtractJobResultInformation $information): JobStatus {
        $response = Request::post(
            $this->controlBaseApiUrl . "/job/index",
            ["AUTH_TOKEN" => Configuration::AuthenticationToken()],
            json_encode($information)
        );
        $std = $this->ValidateResponse($response);
        return RequestDataMapper::ConvertStdToJobStatus($std);
    }

    public function Delete(string $id): JobStatus {
        $response = Request::post(
            $this->controlBaseApiUrl . "/job/remove/".$id,
            ["AUTH_TOKEN" => Configuration::AuthenticationToken()]
        );
        $std = $this->ValidateResponse($response);
        return RequestDataMapper::ConvertStdToJobStatus($std);
    }


}