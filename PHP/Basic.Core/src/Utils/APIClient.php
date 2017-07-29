<?php

namespace AdSearchEngine\Core\Utils;

use AdSearchEngine\Interfaces\Communication\Crawler\Request\CrawlerExtractJobResultInformation;
use AdSearchEngine\Interfaces\Communication\Crawler\Request\CrawlerHarvestJobResultInformation;
use AdSearchEngine\Interfaces\Communication\Crawler\Response\CrawlerJobInformation;
use AdSearchEngine\Interfaces\Communication\Crawler\Response\CrawlerStateInformation;
use AdSearchEngine\Interfaces\Communication\Crawler\Response\ErrorInformation;
use AdSearchEngine\Interfaces\Communication\Search\SearchQuery;
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

    private function ValidateResponse(Response $response) {
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

    public function Search(SearchQuery $query): array
    {
        $response = Request::post(
            $this->controlBaseApiUrl . "/search/search",
            [],
            json_encode($query->jsonSerialize())
        );
        $std = $this->ValidateResponse($response);
        return $std;
    }
}