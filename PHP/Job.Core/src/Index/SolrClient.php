<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 28.12.2016 г.
 * Time: 11:52
 */

namespace Shadows\CarStorage\Core\Index;


use Shadows\CarStorage\Core\Utils\RequestDataMapper;
use Unirest\Request;
use Unirest\Request\Body;
use Unirest\Response;

class SolrClient
{
    private $solrApiUrl;

    /**
     * SolrClient constructor.
     * @param $solrApiUrl
     */
    public function __construct(string $solrApiUrl)
    {
        $this->solrApiUrl = $solrApiUrl;
    }

    private function ValidateResponse(Response $response) {
        if ($response->code != 200) {
            if (!isset($response->body->error))
                throw new \Exception("Unknown error of solr request!");
            if (isset($response->body->error->msg))
                throw new \Exception("Error from SOLR API: {$response->body->error->msg}!");
            throw new \Exception("Error from SOLR API with no message!");
        }
    }

    private function ValidateSelectResponse(Response $response) {
        if (!isset($response->body->responseHeader))
            throw new \Exception("Could not find response header!");
        if (!isset($response->body->responseHeader->status))
            throw new \Exception("Could not find SOLR response status!");
        if ($response->body->responseHeader->status != 0)
            throw new \Exception("SOLR returned unknown status: {$response->body->responseHeader->status}!");
        if (!isset($response->body->response))
            throw new \Exception("Could not find response body!");
    }


    public function AddFileToIndex(JobIndexInformation $information) {
        $postUrl = $this->solrApiUrl . "/update";
        $bodyJSON = $information->jsonSerialize();
        $body = json_encode($bodyJSON);
        $response = Request::post($postUrl,["Content-Type"=>"application/json"],$body);
        $this->ValidateResponse($response);
    }

    public function UpdateDocumentCluster(string $id, int $cluster) {
        $docs = $this->Select("id:$id", 0, 1);
        $doc = $docs[0];
        $indexInformation = RequestDataMapper::ConvertStdToJobIndexInformation($doc);
        $indexInformation->setCluster($cluster);
        $postUrl = rtrim($this->solrApiUrl, "/") . "/update";
        $body = json_encode($indexInformation->jsonSerialize());
        $response = Request::post($postUrl,["Content-Type"=>"application/json"], $body);
        $this->ValidateResponse($response);
    }

    /**
     * @param JobIndexInformation[] $documents
     */
    public function UpdateDocumentArray(array $documents) {
        $postUrl = rtrim($this->solrApiUrl, "/") . "/update";
        $body = [
            "add" => [],
            "commit" => [
                "waitSearcher" => false
            ]
        ];
        foreach ($documents as $doc)
            $body["add"][] = $doc->jsonSerializeClean();
        $body = json_encode($body);
        $response = Request::post($postUrl,["Content-Type"=>"application/json"], $body);
        $this->ValidateResponse($response);
    }

    public function GetDocumentsCount(string $query = "*:*"): int {
        $getURL = rtrim($this->solrApiUrl, "/") . "/select?indent=on&q=$query&rows=0&wt=json";
        $response = Request::get($getURL);
        $this->ValidateResponse($response);
        $this->ValidateSelectResponse($response);
        return $response->body->response->numFound;
    }

    public function Select(string $query, int $start, int $count, string $sort = null): array {
        $URL = rtrim($this->solrApiUrl, "/") . "/select?wt=json";
        $headers = array(
            'Content-Type' => 'application/json'
        );
        $data = array(
            "query" => $query,
            "offset" => $start,
            "limit" => $count
        );
        if (!is_null($sort))
            $data["sort"] = $sort;
        $body = Body::json($data);
        $response = Request::Post($URL, $headers, $body);
        $this->ValidateResponse($response);
        $this->ValidateSelectResponse($response);
        return $response->body->response->docs;
    }
}