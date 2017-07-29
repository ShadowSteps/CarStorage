<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/22/2017
 * Time: 4:09 PM
 */

namespace AdSearchEngine\Core\WebAPI\Controller;


use AdSearchEngine\Core\WebAPI\Controller\Base\BaseAPIController;
use AdSearchEngine\Interfaces\Communication\Search\SearchQuery;
use AdSearchEngine\Interfaces\WebAPI\Controller\ISearchController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SearchController extends BaseAPIController implements ISearchController
{
    /**
     * @Route("/search/search", name="Search")
     * @Method("POST")
     * @ParamConverter(
     *     name="query",
     *     class="AdSearchEngine\Interfaces\Communication\Search\SearchQuery",
     *     converter="search_param_converter"
     * )
     * @param SearchQuery $query
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(SearchQuery $query)
    {
        $code = 200;
        $response = null;
        $this->getLogger()->debug("Search query: ".serialize($query));
        try {
            $response = $this->getIndexServerClient()
                ->Search($query);
        } catch (\InvalidArgumentException $exp) {
            $code = 400;
        } catch (\Exception $exception) {
            $code = 500;
            $this->getLogger()->error("Error while performing search.", $exception);
        }
        return $this->response($response, $code);
    }
}