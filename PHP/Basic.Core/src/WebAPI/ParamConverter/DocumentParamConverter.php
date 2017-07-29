<?php

namespace AdSearchEngine\Core\WebAPI\ParamConverter;

use AdSearchEngine\Core\WebAPI\Utils\RequestHelper;
use AdSearchEngine\Interfaces\Communication\Crawler\Request\CrawlerExtractJobResultInformation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;


class DocumentParamConverter implements ParamConverterInterface
{
    private $adIndexInformationType;

    /**
     * DocumentParamConverter constructor.
     * @param $adIndexInformationType
     */
    public function __construct(string $adIndexInformationType = "AdSearchEngine\\Interfaces\\Index\\AdIndexInformation")
    {
        $this->adIndexInformationType = $adIndexInformationType;
    }

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
        CrawlerExtractJobResultInformation::$adIndexInformationType = $this->adIndexInformationType;
        $std = RequestHelper::GetJsonStdFromRequest($request);
        $request->attributes->set($configuration->getName(), CrawlerExtractJobResultInformation::fromSTD($std));
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
            'AdSearchEngine\Interfaces\Communication\Crawler\Request\CrawlerExtractJobResultInformation'
        ]));
    }
}