<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 27.12.2016 г.
 * Time: 14:53
 */

namespace AppBundle\Controller\Api;


use AppBundle\Controller\Api\Base\BaseAPIController;
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

class JobController extends BaseAPIController
{
    /**
     * @Route("/api/job/register", name="RegisterJob")
     * @Method("POST")
     */
    public function registerAction(Request $request) {
        $response = new JobStatus(true);
        $code = 200;
        try {
            $this->init();
            $std = RequestHelper::GetJsonStdFromRequest($request);
            $requestData = RequestDataMapper::ConvertStdToJobRegistration($std);
            foreach ($requestData->getNewJobs() as $newJob){
                $hash = HashHelper::SHA256($newJob->getUrl());
                try {
                    $existing = $this->getContext()
                        ->getJobSet()
                        ->GetByHash($hash);
                    $date = clone $existing->getDateAdded();
                    $newDate = $date->add(new \DateInterval("P2D"));
                    if ($newDate < new \DateTime() && $existing->isLocked()) {
                        $this->getContext()
                            ->getJobSet()
                            ->UnlockJob($existing->getId());
                    }
                }
                catch (EntityNotFoundException $exp) {
                    $data = new JobData();
                    $data->setJobType($newJob->getJobType());
                    $data->setUrl($newJob->getUrl());
                    $data->setHash($hash);
                    $data->setLocked(false);
                    $this->getContext()
                        ->getJobSet()
                        ->Add($data);
                }
            }
            $this->getContext()
                ->SaveChanges();
        }
        catch (\Exception $exp) {
            $response = new ErrorInformation($exp->getMessage(), get_class($exp));
            $code = 500;
        }
        return $this->response($response, $code);
    }

    /**
     * @Route("/api/job/next",name="GetNextJob")
     * @Method("GET")
     */
    public function getNextAction() {
        $response = null;
        $code = 200;
        try {
            $this->init();
            $job = $this->getContext()
                ->getJobSet()
                ->GetNextFreeJob();
            $this->getContext()
                ->getJobSet()
                ->LockJob($job->getId());
            $this->getContext()
                ->SaveChanges();
            $response = new JobInformation($job->getId(), $job->getUrl(), $job->getJobType());
        }
        catch (NoJobsFoundException $exp) {
            $response = new JobStatus();
        }
        catch (\Exception $exp) {
            $response = new ErrorInformation($exp->getMessage(), get_class($exp));
            $code = 500;
        }
        return $this->response($response, $code);
    }

    /**
     * @Route("/api/job/unlock/{id}", name="UnlockJob")
     * @Method("POST")
     */
    public function unlockAction($id) {
        $response = null;
        $code = 200;
        try {
            $this->getContext()
                ->getJobSet()
                ->UnlockJob($id);
            $this->getContext()
                ->SaveChanges();
            $response = new JobStatus(true);
        }
        catch (\Exception $exp) {
            $response = new ErrorInformation($exp->getMessage(), get_class($exp));
            $code = 500;
        }
        return $this->response($response, $code);
    }
}