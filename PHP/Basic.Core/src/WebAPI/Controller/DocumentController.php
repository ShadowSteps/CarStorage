<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/16/2017
 * Time: 12:21 PM
 */

namespace AdSearchEngine\Core\WebAPI\Controller;


use AdSearchEngine\Core\WebAPI\Controller\Base\IntegrationAPIController;
use AdSearchEngine\Interfaces\Crawler\Communication\Request\CrawlerExtractJobResultInformation;
use AdSearchEngine\Interfaces\Crawler\Communication\Response\CrawlerStateInformation;
use AdSearchEngine\Interfaces\WebAPI\Controller\IDocumentController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;

class DocumentController extends IntegrationAPIController implements IDocumentController
{

    /**
     * @Route("/document/add", name="AddDocument")
     * @Method("POST")
     * @param CrawlerExtractJobResultInformation $jobResultInformation
     * @ParamConverter(
     *     name="jobResultInformation",
     *     class="AdSearchEngine\Interfaces\Crawler\Communication\Request\CrawlerExtractJobResultInformation",
     *     converter="document_param_converter"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction(CrawlerExtractJobResultInformation $jobResultInformation)
    {
        $response = new CrawlerStateInformation(false);
        $code = 200;
        try {
            $this->getRepository()
                ->FinishJob($jobResultInformation->getHarvestJobResultInformation()->getId(), $this->getCrawlerAuthToken());
            $this->getIndexServerClient()
                ->AddFileToIndex($jobResultInformation->getAdIndexInformation());
            $this->getContext()
                ->SaveChanges();
            $response = new CrawlerStateInformation(true);
        }
        catch (BadRequestHttpException $exp){
            $this->getLogger()->warning("Bad request IndexJob.", $exp);
            $code = 400;
        }
        catch (\Exception $exp) {
            $this->getLogger()->error("Internal server error on request IndexJob.", $exp);
            $code = 500;
        }
        return $this->response($response, $code);
    }

    /**
     * @Route("/document/remove/{id}",name="removeDocument")
     * @Method("DELETE")
     * @param string $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(string $id)
    {
        $response = null;
        $code = 200;
        try {
            $this->getContext()
                ->getJobSet()
                ->Delete($id);
            $this->getContext()
                ->SaveChanges();
            $response = new CrawlerStateInformation(true);
        }
        catch (BadRequestHttpException $exp){
            $this->getLogger()->warning("Bad request RemoveJob.", $exp);
            $code = 400;
        }
        catch (\Exception $exp) {
            $this->getLogger()->error("Internal server error on request RemoveJob.", $exp);
            $code = 500;
        }
        return $this->response($response, $code);
    }
}