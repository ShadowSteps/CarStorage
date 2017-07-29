<?php

namespace AdSearchEngine\Core\WebAPI\Controller\Base;

use AdSearchEngine\Interfaces\Data\IAdSearchEngineContext;
use AdSearchEngine\Interfaces\Index\ServerClient\IIndexServerClient;
use AdSearchEngine\Interfaces\Repository\IRepository;
use AdSearchEngine\Interfaces\Utils\ILogger;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\FatalErrorException;

class BaseAPIController extends Controller
{
    public static $contextServiceName = "search_engine.context";
    public static $repositoryServiceName = "search_engine.repository";
    public static $loggerServiceName = "search_engine.logger";
    public static $indexClientServiceName = "search_engine.indexClient";

    /**
     * @var IAdSearchEngineContext
     */
    private $context;

    /**
     * @var IRepository
     */
    private $repository;

    /**
     * @var ILogger
     */
    private $logger;

    /**
     * @var IIndexServerClient
     */
    private $indexServerClient;

    public function getRepository(): IRepository
    {
        if (!isset($this->repository)) {
            if (!$this->has(self::$repositoryServiceName))
                throw new FatalErrorException("Context repository service not registered!");
            $repository = $this->get(self::$repositoryServiceName);
            if (!($repository instanceof IRepository))
                throw new FatalErrorException("Context repository service registered is not of required type!");
            $this->repository = $repository;
        }
        return $this->repository;
    }

    public function getContext(): IAdSearchEngineContext
    {
        if (!isset($this->context)) {
            if (!$this->has(self::$contextServiceName))
                throw new FatalErrorException("Context service not registered!");
            $context = $this->get(self::$contextServiceName);
            if (!($context instanceof IAdSearchEngineContext))
                throw new FatalErrorException("Context service registered is not of required type!");
            $this->context = $context;
        }
        return $this->context;
    }

    public function getLogger(): ILogger
    {
        if (!isset($this->logger)) {
            if (!$this->has(self::$loggerServiceName))
                throw new FatalErrorException("Logger service not registered!");
            $logger = $this->get(self::$loggerServiceName);
            if (!($logger instanceof ILogger))
                throw new FatalErrorException("Logger service registered is not of required type!");
            $this->logger = $logger;
        }
        return $this->logger;
    }

    /**
     * @return IIndexServerClient
     * @throws FatalErrorException
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


    protected function response($object, $status = 200)
    {
        if (is_null($object)) {
            $Response = new Response("", $status);
        } else {
            if ($object instanceof \JsonSerializable)
                $json = json_encode($object->jsonSerialize());
            else
                $json = json_encode($object);
            $Response = new Response($json, $status, ["Content-Type" => "application/json"]);
        }
        return $Response;
    }
}