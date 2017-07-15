<?php

namespace AdSearchEngine\Core\WebAPI\Controller;

use AdSearchEngine\Core\WebAPI\Controller\Base\BaseAPIController;
use AdSearchEngine\Interfaces\Crawler\Communication\Request\CrawlerHarvestJobResultInformation;
use AdSearchEngine\Interfaces\WebAPI\Controller\IJobSchedulerController;

class JobSchedulerController extends BaseAPIController implements IJobSchedulerController
{

    public function finishJobAction(CrawlerHarvestJobResultInformation $jobResultInformation)
    {
        $response = new JobStatus(false);
        $code = 200;
        try {
            $std = RequestHelper::GetJsonStdFromRequest($request);
            $requestData = RequestDataMapper::ConvertStdToJobRegistration($std);
            $this->getRepository()
                ->FinishJob($requestData->getId(), $this->getCrawlerAuthToken());
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
                    $data->setAddedByCrawlerId($this->getCrawlerAuthToken());
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

    public function getNextJobAction()
    {
        // TODO: Implement getNextJobAction() method.
    }
}