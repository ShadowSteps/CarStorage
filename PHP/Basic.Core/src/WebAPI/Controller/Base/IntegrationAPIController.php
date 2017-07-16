<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 5/2/2017
 * Time: 6:16 PM
 */

namespace AdSearchEngine\Core\WebAPI\Controller\Base;


use AdSearchEngine\Interfaces\Index\ServerClient\IIndexServerClient;

class IntegrationAPIController extends BaseAPIController
{
    public static $indexClientServiceName = "search_engine.indexClient";

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
        if (!isset($this->indexServerClient)) {
            if (!$this->has(self::$indexClientServiceName))
                throw new FatalErrorException("Index client service not registered!");
            $indexClient = $this->get(self::$indexClientServiceName);
            if (!($indexClient instanceof IIndexServerClient))
                throw new FatalErrorException("Index client service registered is not of required type!");
            $this->indexServerClient = $indexClient;
        }
        return $this->indexServerClient;
    }

    /**
     * @return mixed
     */
    public function getCrawlerAuthToken()
    {
        return $this->crawlerAuthToken;
    }

    /**
     * @param mixed $crawlerAuthToken
     */
    public function setCrawlerAuthToken(string $crawlerAuthToken)
    {
        $this->crawlerAuthToken = $crawlerAuthToken;
    }

}