<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/22/2017
 * Time: 4:33 PM
 */

namespace AdSearchEngine\Core\WebAPI\ParamConverter;


use AdSearchEngine\Core\WebAPI\Utils\RequestHelper;
use AdSearchEngine\Interfaces\Communication\Search\SearchQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class SearchParamConverter implements ParamConverterInterface
{

    /**
     * Stores the object in the request.
     *
     * @param Request $request The request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $std = RequestHelper::GetJsonStdFromRequest($request);
        $request->attributes->set($configuration->getName(), SearchQuery::fromSTD($std));
        return true;
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration Should be an instance of ParamConverter
     *
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration)
    {
        return (in_array($configuration->getClass(), [
            'AdSearchEngine\Interfaces\Communication\Search\SearchQuery'
        ]));
    }
}