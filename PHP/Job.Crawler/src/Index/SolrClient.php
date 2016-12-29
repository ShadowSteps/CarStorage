<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 28.12.2016 г.
 * Time: 11:52
 */

namespace Shadows\CarStorage\Crawler\Index;


use Unirest\Request;

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


    public function AddFileToIndex(JobIndexInformation $information) {
        $postUrl = $this->solrApiUrl . "/update";
        $bodyJSON = $information->jsonSerialize();
        $body = json_encode($bodyJSON);
        $response = Request::post($postUrl,["Content-Type"=>"application/json"],$body);
        if ($response->code != 200) {
            if (!isset($response->body->error))
                throw new \Exception("Unknown error of solr request!");
            if (isset($response->body->error->msg))
                throw new \Exception("Error from SOLR API: {$response->body->error->msg}!");
            throw new \Exception("Error from SOLR API with no message!");
        }
    }
}