<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 14:53
 */

namespace AppBundle\Controller\Api;


use AppBundle\Controller\Api\Base\BaseAPIController;
use AppBundle\Controller\Api\Base\IntegrationAPIController;
use AppBundle\Utils\Logger;
use AppBundle\Utils\RequestHelper;
use Doctrine\ORM\EntityNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Shadows\CarStorage\Core\Utils\HashHelper;
use Shadows\CarStorage\Data\Interfaces\DTO\Data\JobData;
use Shadows\CarStorage\Core\Communication\ErrorInformation;
use Shadows\CarStorage\Core\Communication\JobInformation;
use Shadows\CarStorage\Core\Communication\JobStatus;
use Shadows\CarStorage\Core\Utils\RequestDataMapper;
use Shadows\CarStorage\Data\Postgres\Exceptions\NoJobsFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class JobController extends IntegrationAPIController
{
    /**
     * @Route("/job/index", name="IndexJob")
     * @Method("POST")
     */
    public function indexAction(Request $request) {
        $response = new JobStatus(false);
        $code = 200;
        try {
            $this->init($request);
            $std = RequestHelper::GetJsonStdFromRequest($request);
            $requestData = RequestDataMapper::ConvertStdToJobExtractResult($std);
            $this->getRepository()
                ->FinishJob($requestData->getJobRegistration()->getId(), $this->getJobCrawlerId());
            $this->getSolrClient()
                ->AddFileToIndex($requestData->getJobIndexInformation());
            $this->getContext()
                ->SaveChanges();
        }
        catch (BadRequestHttpException $exp){
            Logger::warning("Bad request IndexJob.", $exp);
            $code = 400;
        }
        catch (\Exception $exp) {
            Logger::error("Internal server error on request IndexJob.", $exp);
            $code = 500;
        }
        return $this->response($response, $code);
    }
    /**
     * @Route("/job/register", name="RegisterJob")
     * @Method("POST")
     */
    public function registerAction(Request $request) {
        $response = new JobStatus(false);
        $code = 200;
        try {
            $this->init($request);
            $std = RequestHelper::GetJsonStdFromRequest($request);
            $requestData = RequestDataMapper::ConvertStdToJobRegistration($std);
            $this->getRepository()
                ->FinishJob($requestData->getId(), $this->getJobCrawlerId());
            $alreadyAdded = [];
            foreach ($requestData->getNewJobs() as $newJob){
                $hash = HashHelper::SHA256($newJob->getUrl());
                try {
                    $existing = $this->getContext()
                        ->getJobSet()
                        ->GetByHash($hash);
                    $date = clone $existing->getDateAdded();
                    $newDate = $date->add(new \DateInterval("P7D"));
                    if ($newDate < new \DateTime() && $existing->isLocked()) {
                        $this->getContext()
                            ->getJobSet()
                            ->UnlockJob($existing->getId());
                    }
                }
                catch (EntityNotFoundException $exp) {
                    if (isset($alreadyAdded[$hash]))
                        continue;
                    $data = new JobData();
                    $data->setJobType($newJob->getJobType());
                    $data->setUrl($newJob->getUrl());
                    $data->setHash($hash);
                    $data->setLocked(false);
                    $data->setAddedByCrawlerId($this->getJobCrawlerId());
                    $data->setDateAdded(new \DateTime());
                    $this->getContext()
                        ->getJobSet()
                        ->Add($data);
                    $alreadyAdded[$hash] = true;
                }
            }
            $this->getContext()
                ->SaveChanges();
        }
        catch (BadRequestHttpException $exp){
            Logger::warning("Bad request RegisterJob.", $exp);
            $code = 400;
        }
        catch (\Exception $exp) {
            Logger::error("Internal server error on request RegisterJob.", $exp);
            $code = 500;
        }
        return $this->response($response, $code);
    }

    /**
     * @Route("/job/next",name="GetNextJob")
     * @Method("GET")
     */
    public function getNextAction(Request $request) {
        $response = null;
        $code = 200;
        try {
            $this->init($request);
            $job = $this->getContext()
                ->getJobSet()
                ->GetNextFreeJob();
            $this->getContext()
                ->getJobSet()
                ->LockJob($job->getId());
            $this->getContext()
                ->getCrawlerSet()
                ->RegisterCall($this->getJobCrawlerId());
            $this->getContext()
                ->SaveChanges();
            $response = new JobInformation($job->getId(), $job->getUrl(), $job->getJobType());
        }
        catch (BadRequestHttpException $exp){
            Logger::warning("Bad request GetNextJob.", $exp);
            $code = 400;
        }
        catch (NoJobsFoundException $exp) {
            $response = new JobStatus();
        }
        catch (\Exception $exp) {
            Logger::error("Internal server error on request GetNextJob.", $exp);
            $code = 500;
        }
        return $this->response($response, $code);
    }

    /**
     * @Route("/job/remove/{id}",name="RemoveJob")
     * @Method("POST")
     */
    public function removeAction(Request $request, $id) {
        $response = null;
        $code = 200;
        try {
            $this->init($request);
            $this->getContext()
                ->getJobSet()
                ->Delete($id);
            $this->getContext()
                ->SaveChanges();
            $response = new JobStatus(true);
        }
        catch (BadRequestHttpException $exp){
            Logger::warning("Bad request RemoveJob.", $exp);
            $code = 400;
        }
        catch (\Exception $exp) {
            Logger::error("Internal server error on request RemoveJob.", $exp);
            $code = 500;
        }
        return $this->response($response, $code);
    }
}