<?php

namespace AdSearchEngine\Core\WebAPI\Controller\Base;

use AdSearchEngine\Interfaces\Data\IAdSearchEngineContext;
use AdSearchEngine\Interfaces\Repository\IRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class BaseAPIController extends Controller
{
    /**
     * @var IAdSearchEngineContext
     */
    private $context;

    /**
     * @var IRepository
     */
    private $repository;

    /**
     * @return IRepository
     */
    public function getRepository(): IRepository
    {
        return $this->repository;
    }

    /**
     * @return IAdSearchEngineContext
     */
    public function getContext(): IAdSearchEngineContext
    {
        return $this->context;
    }


    protected function response($object, $status = 200)
    {
        if (is_null($object)) {
            $Response = new Response("", $status);
        } else {
            $json = json_encode($object);
            $Response = new Response($json, $status, ["Content-Type" => "application/json"]);
        }
        return $Response;
    }
}