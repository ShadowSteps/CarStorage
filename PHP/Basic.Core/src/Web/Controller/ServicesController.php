<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/29/2017
 * Time: 11:51 AM
 */

namespace AdSearchEngine\Core\Web\Controller;


use AdSearchEngine\Interfaces\Communication\Search\Common\FieldRangeCriteria;
use AdSearchEngine\Interfaces\Communication\Search\Common\FieldSearchCriteria;
use AdSearchEngine\Interfaces\Communication\Search\SearchQuery;
use AdSearchEngine\Interfaces\Utils\IAPIClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\FatalErrorException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Unirest\Request\Body;

class ServicesController extends Controller
{
    public static $apiClientServiceName = "search_engine.api_client";
    /**
     * @var IAPIClient
     */
    private $client;

    /**
     * @return IAPIClient
     * @throws FatalErrorException
     */
    public function getClient(): IAPIClient
    {
        if (!isset($this->client)) {
            if (!$this->has(self::$apiClientServiceName))
                throw new HttpException(500, "API client service not registered!");
            $client = $this->get(self::$apiClientServiceName);
            if (!($client instanceof IAPIClient))
                throw new HttpException(500, "API client service registered is not of required type!");
            $this->client = $client;
        }
        return $this->client;
    }

    /**
     * @Route("/services/search", name="SearchQuery")
     * @param Request $request
     * @return Response
     */
    public function searchAction(Request $request)
    {
        $searchText = $request->get("text", "*");
        $fieldsArray = $request->get("fields");
        if (!is_array($fieldsArray))
            return new Response(json_encode([]));
        $itemsPerPage = $request->get("pageItems", 10);
        $page = $request->get("page", 1);
        $searchQuery = new SearchQuery($itemsPerPage, $page);
        foreach ($fieldsArray as list($name, $weight))
            $searchQuery->addSearchCriteria(new FieldSearchCriteria($name, $weight, $searchText));
        $rangeArray = $request->get("range");
        if (is_array($rangeArray))
        {
            foreach ($rangeArray as $name => list($min, $max))
                $searchQuery->addRangeCriteria(new FieldRangeCriteria($name, $max, $min));
        }
        $body = $this->getClient()->Search($searchQuery);
        $jsonBody = Body::Json($body);
        return new Response($jsonBody);
    }


}