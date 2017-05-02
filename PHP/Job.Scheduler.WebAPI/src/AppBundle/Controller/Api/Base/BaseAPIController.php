<?php

namespace AppBundle\Controller\Api\Base;

use AppBundle\Interfaces\Repository\IRepository;
use AppBundle\Repository\Repository;
use AppBundle\Utils\Logger;
use Shadows\CarStorage\Data\Interfaces\IJobSchedulerContext;
use Shadows\CarStorage\Data\Postgres\JobSchedulerContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BaseAPIController extends Controller
{
    /**
     * @var IJobSchedulerContext
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



    protected function init(Request $request)
    {
        if (!$this->has("logger"))
            throw new \Exception("Logger does not exist!");
        /**
         * @var $logger \Monolog\Logger
         */
        $logger = $this->get("logger");
        Logger::init($logger);
        $this->context = new JobSchedulerContext(
            $this->getParameter("database_host"),
            $this->getParameter("database_user"),
            $this->getParameter("database_password"),
            $this->getParameter("database_name"),
            $this->getParameter("database_port")
        );
        $this->repository = new Repository($this->context);
    }

    /**
     * @return IJobSchedulerContext
     */
    public function getContext(): IJobSchedulerContext
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