<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 5/15/2017
 * Time: 11:20 AM
 */

namespace Shadows\CarStorage\Core\ML;


use Phpml\NeuralNetwork\Network\MultilayerPerceptron;
use Phpml\NeuralNetwork\Training\Backpropagation;
use Phpml\Regression\SVR;
use Shadows\CarStorage\Core\Index\SolrClient;
use Shadows\CarStorage\Core\ML\Feature\Feature;
use Shadows\CarStorage\Core\ML\Feature\IndexFeatureExtractor;

class IndexRegression
{
    /**
     * @var SVR
     */
    private $model;

    private function Train(SolrClient $client, int $step = 100){
        $this->model = null;
        $this->model = new SVR();
        $featureExtractor = new IndexFeatureExtractor($client);
        $features = $featureExtractor->getFeatureVector();
        $documentsCount = 1000;//$client->GetDocumentsCount();
        $documents = [];
        $results = [];
        for ($i = 0; $i < $documentsCount; $i += $step) {
            $rawDocuments = $client->Select("*:*", $i, $step, "id asc");
            foreach ($rawDocuments as $key => $doc) {
                $convertedDoc = [];
                foreach ($features as $feature) {
                    /**
                     * @var $feature Feature
                     */
                    $convertedDoc[] = $doc->{$feature->getName()}/300000;
                }
                if ($convertedDoc[0] > 1)
                    continue;
                if ($doc->pric > 50000)
                    continue;
                $documents[] = $convertedDoc;
                $results[] = ($doc->price / 50000);
            }
        }
        $this->model->train($documents, $results);
    }

    private function Load(string $path) {
        if (!file_exists($path))
            throw new \Exception("File not found containing model!");
        $content = file_get_contents($path);
        if (mb_strlen($content) <= 0)
            throw new \Exception("File does not contain content!");
        $model = unserialize($content);
        if (!($model instanceof SVR))
            throw new \Exception("File does not contain model definition!");
        $this->model = $model;
    }

    public function LoadOrTrain(SolrClient $client, string $path, int $trainingStep = 100) {
        try {
            $this->Load($path);
        } catch (\Exception $exception) {
            $this->Train($client, $trainingStep);
        }
    }

    public function Predict(array $values) {
        return $this->model->predict($values);
    }
}