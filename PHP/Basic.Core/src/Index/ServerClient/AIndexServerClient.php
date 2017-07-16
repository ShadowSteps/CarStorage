<?php

namespace AdSearchEngine\Core\Index\ServerClient;

use AdSearchEngine\Interfaces\Index\ServerClient\IIndexServerClient;
use Unirest\Request;
use Unirest\Response;

abstract class AIndexServerClient implements IIndexServerClient
{
    private $apiUrl;
    private $queryMaxTries = 5;

    /**
     * @param int $queryMaxTries
     */
    public function setQueryMaxTries(int $queryMaxTries)
    {
        $this->queryMaxTries = $queryMaxTries;
    }

    public function __construct(string $apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    /**
     * @return int
     */
    public function getQueryMaxTries(): int
    {
        return $this->queryMaxTries;
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    abstract protected function ValidateResponse(Response $response);
    abstract protected function ValidateSelectResponse(Response $response);

    protected function doGET(string $url): Response {
        $i = 1;
        $response = null;
        while ($i <= $this->queryMaxTries) {
            try {
                $response = Request::get($url);
                break;
            } catch (\Exception $exception) {
                if ($i == $this->queryMaxTries)
                    throw $exception;
                $i++;
                sleep(1);
            }
        }
        $this->ValidateResponse($response);
        $this->ValidateSelectResponse($response);
        return $response;
    }

    protected function doPOST(string $url, $body = null, array $headers = []): Response {
        $i = 1;
        $response = null;
        while ($i <= $this->queryMaxTries) {
            try {
                $response = Request::Post($url, $headers, $body);
                break;
            } catch (\Exception $exception) {
                if ($i == $this->queryMaxTries)
                    throw $exception;
                $i++;
                sleep(1);
            }
        }
        $this->ValidateResponse($response);
        return $response;
    }
}