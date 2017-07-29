<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 28.12.2016 г.
 * Time: 11:52
 */

namespace AdSearchEngine\Core\Index\ServerClient;


use AdSearchEngine\Core\Index\ServerClient\Utils\SOLRQueryGenerator;
use AdSearchEngine\Interfaces\Communication\Search\SearchQuery;
use AdSearchEngine\Interfaces\Index\AdIndexInformation;
use Unirest\Request\Body;
use Unirest\Response;

class SOLRClient extends AIndexServerClient
{
    protected function ValidateResponse(Response $response) {
        if ($response->code != 200) {
            if (!isset($response->body->error))
                throw new \Exception("Unknown error of solr request! Body: ".$response->raw_body);
            if (isset($response->body->error->msg))
                throw new \Exception("Error from SOLR API: {$response->body->error->msg}!");
            throw new \Exception("Error from SOLR API with no message!");
        }
    }

    protected function ValidateSelectResponse(Response $response) {
        if (!isset($response->body->responseHeader))
            throw new \Exception("Could not find response header!");
        if (!isset($response->body->responseHeader->status))
            throw new \Exception("Could not find SOLR response status!");
        if ($response->body->responseHeader->status != 0)
            throw new \Exception("SOLR returned unknown status: {$response->body->responseHeader->status}!");
        if (!isset($response->body->response))
            throw new \Exception("Could not find response body!");
    }

    public function AddFileToIndex(AdIndexInformation $information): void {
        $postUrl = $this->getApiUrl() . "/update";
        $bodyJSON = $information->jsonSerialize();
        $body = [
            "add" => [
                $bodyJSON
            ],
            "commit" => [
                "waitSearcher" => false
            ]
        ];
        $body = json_encode($body);
        $response = $this->doPOST($postUrl, $body, ["Content-Type"=>"application/json"]);
        $this->ValidateResponse($response);
    }

    /**
     * @param AdIndexInformation[] $documents
     */
    public function UpdateDocumentArray(array $documents): void {
        $postUrl = rtrim($this->getApiUrl(), "/") . "/update";
        $body = [
            "add" => [],
            "commit" => [
                "waitSearcher" => false
            ]
        ];
        foreach ($documents as $doc)
            $body["add"][] = $doc->jsonSerialize();
        $body = json_encode($body);
        $response = $this->doPOST($postUrl, $body, ["Content-Type"=>"application/json"]);
        $this->ValidateResponse($response);
    }

    private function NormalizeQuery(string $query, bool $urlEncode = true): string {
        $query = str_replace(["/","\\"],"",$query);
        $query = preg_replace("/\"+/","\"",$query);
        $query = trim($query);
        return $urlEncode ? urlencode($query) : $query;
    }

    public function GetDocumentsCount(string $query = "*:*"): int {
        $getURL = rtrim($this->getApiUrl(), "/") . "/select?indent=on&q=".$this->NormalizeQuery($query)."&rows=0&wt=json";
        $response = $this->doGET($getURL);
        $this->ValidateSelectResponse($response);
        return $response->body->response->numFound;
    }

    public function Select(string $query, int $start, int $count, string $sort = null): array {
        $URL = rtrim($this->getApiUrl(), "/") . "/select?wt=json";
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
        $response = $this->doPOST($URL, $body, $headers);
        $this->ValidateSelectResponse($response);
        return $response->body->response->docs;
    }

    public function GetMaxOfNumericFeature(string $feature): float {
        $getURL = rtrim($this->getApiUrl(), "/") . "/select?indent=on&q=*:*&rows=1&wt=json&sort=$feature desc";
        $response = $this->doGET($getURL);
        $this->ValidateSelectResponse($response);
        return $response->body->response->docs[0]->{$feature};
    }

    public function GetMinOfNumericFeature(string $feature): float {
        $getURL = rtrim($this->getApiUrl(), "/") . "/select?indent=on&q=*:*&rows=1&wt=json&sort=$feature asc";
        $response = $this->doGET($getURL);
        $this->ValidateSelectResponse($response);
        return $response->body->response->docs[0]->{$feature};
    }

    public function GetAverageOfNumericFeature(string $feature, string $query = "*:*"): float {
        $getURL = rtrim($this->getApiUrl(), "/") . "/select?indent=on&q=".$this->NormalizeQuery($query)."&rows=0&wt=json&json.facet={\"x\":\"avg($feature)\"}";
        $response = $this->doGET($getURL);
        $this->ValidateSelectResponse($response);
        return $response->body->facets->x;
    }

    public function GetSigmaDispersionOfNumericFeature(string $feature): float {
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

    public function UpdateDocumentField(string $documentId, string $fieldName, $value): void
    {
        $this->UpdateDocumentArrayField($fieldName, [$documentId => $value]);
    }

    public function UpdateDocumentArrayField(string $fieldName, array $values): void
    {
        $postUrl = rtrim($this->getApiUrl(), "/") . "/update";
        $body = [
            "update" => [],
            "commit" => [
                "waitSearcher" => false
            ]
        ];
        foreach ($values as $documentId => $value)
            $body["update"][] = ["id" => $documentId, $fieldName => ["set" => $value]];
        $body = json_encode($body);
        $response = $this->doPOST($postUrl, $body, ["Content-Type"=>"application/json"]);
        $this->ValidateResponse($response);
    }

    public function SelectDocumentById(string $documentId): \stdClass
    {
        return $this->Select("id:$documentId", 0, 1)[0];
    }

    public function DeleteById(string $documentId): void
    {
        $postUrl = rtrim($this->getApiUrl(), "/") . "/update";
        $body = [
            "delete" => [
                "id" => $documentId
            ],
            "commit" => [
                "waitSearcher" => false
            ]
        ];
        $body = json_encode($body);
        $response = $this->doPOST($postUrl, $body, ["Content-Type"=>"application/json"]);
        $this->ValidateResponse($response);
    }

    public function Search(SearchQuery $query): array
    {
        $searchCriteria = $query->getFieldsSearchCriteria();
        $q = "";
        $qf = "";
        $fq = [];
        foreach ($searchCriteria as $criteria) {
            $qf .= $criteria->getFieldName() . "^" . $criteria->getFieldWeight() . " ";
            $words = explode(" ",$criteria->getSearchTerm());
            foreach ($words as $word) {
                $word = mb_strtolower($word);
                if (!mb_strstr($q, $word))
                    $q .= ' ' . $word;
            }
        }
        foreach ($query->getFieldsRangeCriteria() as $criterion) {
            $fq[] = ($criterion->getFieldName().":[".$criterion->getFieldMin()." TO ".$criterion->getFieldMax()."]");
        }
        $q = (trim($q));
        $qf = (trim($qf));
        $URL = rtrim($this->getApiUrl(), "/") . "/select?wt=json&defType=edismax&qf=$qf";
        $headers = array(
            'Content-Type' => 'application/json'
        );
        $data = array(
            "query" => $q,
            "offset" => (($query->getPage() - 1)*$query->getItemsPerPage()),
            "limit" => $query->getItemsPerPage(),
            "filter" => $fq
        );
        $response = $this->doPOST($URL, json_encode($data), $headers);
        $this->ValidateSelectResponse($response);
        return $response->body->response->docs;
    }
}