<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 5/2/2017
 * Time: 6:16 PM
 */

namespace AdSearchEngine\Core\WebAPI\Controller\Base;

class IntegrationAPIController extends BaseAPIController
{

    private $crawlerAuthToken;

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