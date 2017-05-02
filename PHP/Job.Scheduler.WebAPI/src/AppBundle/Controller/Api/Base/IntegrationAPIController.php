<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 5/2/2017
 * Time: 6:16 PM
 */

namespace AppBundle\Controller\Api\Base;


use Shadows\CarStorage\Core\Index\SolrClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class IntegrationAPIController extends BaseAPIController
{
    private $jobCrawlerId;
    /**
     * @var SolrClient
     */
    private $solrClient;

    /**
     * @return SolrClient
     */
    public function getSolrClient(): SolrClient
    {
        return $this->solrClient;
    }
    /**
     * @return mixed
     */
    public function getJobCrawlerId()
    {
        return $this->jobCrawlerId;
    }

    protected function init(Request $request)
    {
        parent::init($request);
        if (!$request->headers->has("AUTH_TOKEN"))
            throw new BadRequestHttpException();
        $crawlerId =  $request->headers->get("AUTH_TOKEN");
        if (!$this->getContext()->getCrawlerSet()->Exists($crawlerId))
            throw new BadRequestHttpException();
        $this->jobCrawlerId = $crawlerId;
        $this->solrClient = new SolrClient($this->getParameter("solr.client.url"));
    }
}