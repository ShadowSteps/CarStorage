<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 5/2/2017
 * Time: 6:16 PM
 */

namespace AdSearchEngine\Core\WebAPI\Controller\Base;


use AdSearchEngine\Core\WebAPI\Controller\Base\BaseAPIController;
use AdSearchEngine\Interfaces\Index\ServerClient\IIndexServerClient;
use Shadows\CarStorage\Core\Index\SOLRClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class IntegrationAPIController extends BaseAPIController
{
    private $crawlerAuthToken;
    /**
     * @var IIndexServerClient
     */
    private $indexServerClient;

    /**
     * @return IIndexServerClient
     */
    public function getIndexServerClient(): IIndexServerClient
    {
        return $this->indexServerClient;
    }
    /**
     * @return mixed
     */
    public function getCrawlerAuthToken()
    {
        return $this->crawlerAuthToken;
    }

    protected function init(Request $request)
    {
        if (!$request->headers->has("AUTH_TOKEN"))
            throw new BadRequestHttpException();
        $crawlerId =  $request->headers->get("AUTH_TOKEN");
        if (!$this->getContext()->getCrawlerSet()->Exists($crawlerId))
            throw new BadRequestHttpException();
        $this->crawlerAuthToken = $crawlerId;
    }
}