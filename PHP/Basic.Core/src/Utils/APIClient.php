<?php

namespace AdSearchEngine\Core\Utils;

use AdSearchEngine\Interfaces\Crawler\Communication\Request\CrawlerExtractJobResultInformation;
use AdSearchEngine\Interfaces\Crawler\Communication\Request\CrawlerHarvestJobResultInformation;
use AdSearchEngine\Interfaces\Crawler\Communication\Response\CrawlerJobInformation;
use AdSearchEngine\Interfaces\Crawler\Communication\Response\CrawlerStateInformation;
use AdSearchEngine\Interfaces\Crawler\Communication\Response\ErrorInformation;
use AdSearchEngine\Interfaces\Utils\IAPIClient;
use Unirest\Request;
use Unirest\Response;

class APIClient implements IAPIClient
{
    private $controlBaseApiUrl;
    private $authorizationToken;

    public function __construct(string $controlBaseApiUrl, string $authToken)
    {
        $this->controlBaseApiUrl = $controlBaseApiUrl;
        $this->authorizationToken = $authToken;
    }

    private function ValidateResponse(Response $response) : \stdClass {
        $content = $response->raw_body;
        if (strlen($content) <= 0)
            throw new \Exception("Response is empty!");
        $std = json_decode($content);
        if (!$std)
            throw new \Exception("Response is not json format!");
        if ($response->code != 200)
        {
            try {
                $error = ErrorInformation::fromSTD($response->body);
                $message = "Error receive from server({$error->getExceptionType()}): {$error->getMessage()}!";
            } catch (\Exception $exp) {
                $message = "Could not parse response from server!";
            }
            throw new \Exception($message);
        }
        return $std;
    }

    public function GetNextJob(): CrawlerStateInformation {
        $response = Request::get(
            $this->controlBaseApiUrl . "/job/next",
            ["AUTH_TOKEN" => $this->authorizationToken]
        );
        $std = $this->ValidateResponse($response);
        $status = CrawlerStateInformation::fromSTD($std);
        if (!$status->isStatus())
            return $status;
        return CrawlerJobInformation::fromSTD($std);
    }

    public function Register(CrawlerHarvestJobResultInformation $registration): JobStatus {
        $response = Request::post(
            $this->controlBaseApiUrl . "/job/register",
            ["AUTH_TOKEN" => $this->authorizationToken],
            json_encode($registration->jsonSerialize())
        );
        $std = $this->ValidateResponse($response);
        return CrawlerStateInformation::fromSTD($std);
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


    public function GetNextCrawlerJob(): CrawlerStateInformation
    {
        $response = Request::get(
            $this->controlBaseApiUrl . "/job/next",
            ["AUTH_TOKEN" => $this->authorizationToken]
        );
        $std = $this->ValidateResponse($response);
        $status = CrawlerStateInformation::fromSTD($std);
        if (!$status->isActive())
            return $status;
        return CrawlerJobInformation::fromSTD($std);
    }

    public function RegisterNewCrawlerJobs(CrawlerHarvestJobResultInformation $registration): CrawlerStateInformation
    {
        $response = Request::post(
            $this->controlBaseApiUrl . "/job/register",
            ["AUTH_TOKEN" => $this->authorizationToken],
            json_encode($registration->jsonSerialize())
        );
        $std = $this->ValidateResponse($response);
        return CrawlerStateInformation::fromSTD($std);
    }

    public function AddDocument(CrawlerExtractJobResultInformation $information): CrawlerStateInformation
    {
        $response = Request::post(
            $this->controlBaseApiUrl . "/document/add",
            ["AUTH_TOKEN" => $this->authorizationToken],
            json_encode($information->jsonSerialize())
        );
        $std = $this->ValidateResponse($response);
        return CrawlerStateInformation::fromSTD($std);
    }

    public function DeleteDocument(string $id): CrawlerStateInformation
    {
        $response = Request::delete(
            $this->controlBaseApiUrl . "/document/remove/".$id,
            ["AUTH_TOKEN" => $this->authorizationToken]
        );
        $std = $this->ValidateResponse($response);
        return CrawlerStateInformation::fromSTD($std);
    }
}