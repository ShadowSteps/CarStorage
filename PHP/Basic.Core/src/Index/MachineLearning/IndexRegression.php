<?php

namespace AdSearchEngine\Core\Index\MachineLearning;

use AdSearchEngine\Interfaces\Index\ServerClient\IIndexServerClient;
use AdSearchEngine\Interfaces\Index\MachineLearning\IIndexRegression;
use AdSearchEngine\Interfaces\Index\MachineLearning\RegressionModel\IIndexRegressionModel;

class IndexRegression implements IIndexRegression
{
    private $documentHelper;
    private $indexServerClient;
    private $targetFeature;

    public function __construct(IIndexServerClient $client, array $features, string $targetFeature)
    {
        foreach ($features as $feature)
            if ($feature->getName() == $targetFeature)
            {
                $this->targetFeature = $feature;
                break;
            }
        $this->documentHelper = new DocumentConvertHelper($features);
        $this->indexServerClient = $client;
    }

    public function predictDocumentFeature(IIndexRegressionModel $model, string $predictionFeature, string $documentId)
    {
        $docs = $this->indexServerClient->SelectDocumentById($documentId);
        $priceCoefficient = $model->predict([$docs])[0];
        if ($priceCoefficient == null)
            return null;
        return $this->targetFeature->denormalize($priceCoefficient);
    }
}