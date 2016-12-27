<?php

namespace AppBundle\Controller\Api\Base;


use Shadows\CarStorage\Data\Interfaces\IJobSchedulerContext;
use Shadows\CarStorage\Data\Postgres\JobSchedulerContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class BaseAPIController extends Controller
{
    /**
     * @var IJobSchedulerContext
     */
    private $context;
    protected function init()
    {
        $this->context = new JobSchedulerContext(
            $this->getParameter("database_host"),
            $this->getParameter("database_user"),
            $this->getParameter("database_password"),
            $this->getParameter("database_name"),
            $this->getParameter("database_port")
        );
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
        $json = json_encode($object);
        $Response = new Response($json, $status, ["Content-Type" => "application/json"]);
        return $Response;
    }
}