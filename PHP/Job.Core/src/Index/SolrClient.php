<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 28.12.2016 г.
 * Time: 11:52
 */

namespace Shadows\CarStorage\Core\Index;


use Shadows\CarStorage\Core\ML\Feature\NumericFeatureCharacteristics;
use Shadows\CarStorage\Core\Utils\RequestDataMapper;
use Unirest\Request;
use Unirest\Request\Body;
use Unirest\Response;

class SolrClient
{
    private $solrApiUrl;
    public $queryMaxTries = 5;
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

    private function GetRequest(string $url): Response {
        $i = 1;
        $response = "";
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

    private function PostRequest(string $url, $body = null, array $headers = []): Response {
        $i = 1;
        $response = "";
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

    public function AddFileToIndex(JobIndexInformation $information) {
        $postUrl = $this->solrApiUrl . "/update";
        $bodyJSON = $information->jsonSerialize();
        $body = json_encode($bodyJSON);
        $response = $this->PostRequest($postUrl, $body, ["Content-Type"=>"application/json"]);
        $this->ValidateResponse($response);
    }

    public function UpdateDocumentCluster(string $id, int $cluster) {
        $docs = $this->Select("id:$id", 0, 1);
        $doc = $docs[0];
        $indexInformation = RequestDataMapper::ConvertStdToJobIndexInformation($doc);
        $indexInformation->setCluster($cluster);
        $postUrl = rtrim($this->solrApiUrl, "/") . "/update";
        $body = json_encode($indexInformation->jsonSerialize());
        $response = $this->PostRequest($postUrl, $body, ["Content-Type"=>"application/json"]);
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
        $response = $this->PostRequest($postUrl, $body, ["Content-Type"=>"application/json"]);
        $this->ValidateResponse($response);
    }

    public function NormalizeQuery(string $query, bool $urlEncode = true): string {
        $query = str_replace(["/","\\"],"",$query);
        $query = trim($query);
        return $urlEncode ? urlencode($query) : $query;
    }

    public function GetDocumentsCount(string $query = "*:*"): int {
        $getURL = rtrim($this->solrApiUrl, "/") . "/select?indent=on&q=".$this->NormalizeQuery($query)."&rows=0&wt=json";
        $response = $this->GetRequest($getURL);
        $this->ValidateSelectResponse($response);
        return $response->body->response->numFound;
    }

    public function Select(string $query, int $start, int $count, string $sort = null): array {
        $URL = rtrim($this->solrApiUrl, "/") . "/select?wt=json";
        $headers = array(
            'Content-Type' => 'application/json'
        );
        $data = array(
            "query" => $this->NormalizeQuery($query, false),
            "offset" => $start,
            "limit" => $count
        );
        if (!is_null($sort))
            $data["sort"] = $sort;
        $body = Body::json($data);
        $response = $this->PostRequest($URL, $body, $headers);
        $this->ValidateSelectResponse($response);
        return $response->body->response->docs;
    }

    public function GetMaxOfNumericFeature(string $feature) {
        $getURL = rtrim($this->solrApiUrl, "/") . "/select?indent=on&q=*:*&rows=1&wt=json&sort=$feature desc";
        $response = $this->GetRequest($getURL);
        $this->ValidateSelectResponse($response);
        return $response->body->response->docs[0]->{$feature};
    }

    public function GetMinOfNumericFeature(string $feature) {
        $getURL = rtrim($this->solrApiUrl, "/") . "/select?indent=on&q=*:*&rows=1&wt=json&sort=$feature asc";
        $response = $this->GetRequest($getURL);
        $this->ValidateSelectResponse($response);
        return $response->body->response->docs[0]->{$feature};
    }

    public function GetAverageOfNumericFeature(string $feature, string $query = "*:*") {
        $getURL = rtrim($this->solrApiUrl, "/") . "/select?indent=on&q=".$this->NormalizeQuery($query)."&rows=0&wt=json&json.facet={\"x\":\"avg($feature)\"}";
        $response = $this->GetRequest($getURL);
        $this->ValidateSelectResponse($response);
        return $response->body->facets->x;
    }

    public function GetSigmaDispersionOfNumericFeature(string $feature) {
        $average = $this->GetAverageOfNumericFeature($feature);
        $step = 100;
        $documentsCount = $this->GetDocumentsCount();
        $sigmaSquared = 0;
        for ($i = 0; $i < $documentsCount; $i += $step) {
            $rawDocuments = $this->Select("*:*", $i, $step, "id asc");
            foreach ($rawDocuments as $key => $doc)
                $sigmaSquared += pow($doc->{$feature} - $average, 2) / $documentsCount;
        }
        return $sigmaSquared;
    }

    private function GetNumericFeatureValueByElementKey(string $feature, float $key, string $query = "*:*"): float {
        $value = null;
        if (($key - (int) $key == 0.5)) {
            $bottom = (int)floor($key);
            $up = (int)ceil($key);
            $upperDocument = $this->Select($query, $up, 1, "$feature asc");
            $bottomDocument = $this->Select($query, $bottom, 1, "$feature asc");
            $value = ($upperDocument[0]->{$feature} + $bottomDocument[0]->{$feature})/2;
        } else {
            $document = $this->Select($query, (int)$key, 1, "$feature asc");
            $value = $document[0]->{$feature};
        }
        return $value;
    }

    public function GetMedianOfNumericFeature(string $feature, string $query = "*:*"): float {
        $documentsCount = $this->GetDocumentsCount($query);
        $key = ($documentsCount + 1) / 2;
        return $this->GetNumericFeatureValueByElementKey($feature, $key, $query);
    }

    public function GetFirstQuartileOfNumericFeature(string $feature, string $query = "*:*"): float {
        $documentsCount = $this->GetDocumentsCount($query);
        $key = ($documentsCount + 1) / 4;
        return $this->GetNumericFeatureValueByElementKey($feature, $key, $query);
    }

    public function GetThirdQuartileOfNumericFeature(string $feature, string $query = "*:*"): float {
        $documentsCount = $this->GetDocumentsCount($query);
        $key = 3 * ($documentsCount + 1) / 4;
        return $this->GetNumericFeatureValueByElementKey($feature, $key, $query);
    }
}