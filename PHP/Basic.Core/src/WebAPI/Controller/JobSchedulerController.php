<?php

namespace AdSearchEngine\Core\WebAPI\Controller;

use AdSearchEngine\Core\Data\Postgres\Exceptions\NoJobsFoundException;
use AdSearchEngine\Core\Utils\HashHelper;
use AdSearchEngine\Core\WebAPI\Controller\Base\IntegrationAPIController;
use AdSearchEngine\Interfaces\Communication\Crawler\Request\CrawlerHarvestJobResultInformation;
use AdSearchEngine\Interfaces\Communication\Crawler\Response\CrawlerJobInformation;
use AdSearchEngine\Interfaces\Communication\Crawler\Response\CrawlerStateInformation;
use AdSearchEngine\Interfaces\Data\DTO\Data\JobData;
use AdSearchEngine\Interfaces\WebAPI\Controller\IJobSchedulerController;
use Doctrine\ORM\EntityNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;

class JobSchedulerController extends IntegrationAPIController implements IJobSchedulerController
{
    /**
     * @Route("/job/register", name="FinishJob")
     * @Method("POST")
     * @param CrawlerHarvestJobResultInformation $jobResultInformation
     * @ParamConverter(
     *     name="jobResultInformation",
     *     class="AdSearchEngine\Interfaces\Communication\Crawler\Request\CrawlerHarvestJobResultInformation",
     *     converter="job_scheduler_param_converter"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function finishJobAction(CrawlerHarvestJobResultInformation $jobResultInformation)
    {
        $response = new CrawlerStateInformation(false);
        $code = 200;
        try {
            $this->getRepository()
                ->FinishJob($jobResultInformation->getId(), $this->getCrawlerAuthToken());
            $alreadyAdded = [];
            foreach ($jobResultInformation->getNewJobs() as $newJob){
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
            $this->getLogger()->warning("Bad request RegisterJob.", $exp);
            $code = 400;
        }
        catch (\Exception $exp) {
            $this->getLogger()->error("Internal server error on request RegisterJob.", $exp);
            $code = 500;
        }
        return $this->response($response, $code);
    }

    /**
     * @Route("/job/next",name="GetNextJob")
     * @Method("GET")
     */
    public function getNextJobAction()
    {
        $response = null;
        $code = 200;
        try {
            $job = $this->getContext()
                ->getJobSet()
                ->GetNextFreeJob();
            $this->getContext()
                ->getJobSet()
                ->LockJob($job->getId());
            $this->getContext()
                ->getCrawlerSet()
                ->RegisterCall($this->getCrawlerAuthToken());
            $this->getContext()
                ->SaveChanges();
            $response = new CrawlerJobInformation($job->getId(), $job->getUrl(), $job->getJobType());
        }
        catch (BadRequestHttpException $exp){
            $this->getLogger()->warning("Bad request GetNextJob.", $exp);
            $code = 400;
        }
        catch (NoJobsFoundException $exp) {
            $response = new CrawlerStateInformation();
        }
        catch (\Exception $exp) {
            $this->getLogger()->error("Internal server error on request GetNextJob.", $exp);
            $code = 500;
        }
        return $this->response($response, $code);
    }
}