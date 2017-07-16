<?php

namespace AdSearchEngine\Core\Index\MachineLearning\FeatureExtraction;

use AdSearchEngine\Core\Index\IndexDocumentsQueue;
use AdSearchEngine\Core\Index\MachineLearning\Feature\BooleanNumericFeature;
use AdSearchEngine\Core\Index\MachineLearning\Feature\NumericFeature;
use AdSearchEngine\Core\Index\MachineLearning\Feature\NumericFeatureCharacteristics;
use AdSearchEngine\Core\Index\MachineLearning\FeatureExtraction\FeatureOption\TextFeatureOption;
use AdSearchEngine\Interfaces\Index\ServerClient\IIndexServerClient;

class IndexFeatureExtractor
{
    private $indexServerClient;
    private $numericFeatures = [];
    /**
     * @var TextFeatureOption[]
     */
    private $textFeatures = [];
    private $additionalFeaturesCache = [];

    public function __construct(IIndexServerClient $serverClient)
    {
        $this->indexServerClient = $serverClient;
    }

    public function addNumericFeature(string $name) {
        $this->numericFeatures[] = $name;
    }

    public function addTextFeature(TextFeatureOption $featureOption) {
        $this->textFeatures[] = $featureOption;
    }

    public function getIndexServerClient(): IIndexServerClient
    {
        return $this->indexServerClient;
    }

    private function getNumericFeaturesCharacteristics(string $feature): NumericFeatureCharacteristics {
        $firstQuartile = $this->getIndexServerClient()->GetFirstQuartileOfNumericFeature($feature);
        $thirdQuartile = $this->getIndexServerClient()->GetThirdQuartileOfNumericFeature($feature);
        return new NumericFeatureCharacteristics($firstQuartile, $thirdQuartile);
    }

    protected function getFeatureFromWord(string $word, int $minSupportCount, int $docCount, string $type) {
        if (is_numeric($word))
            return null;
        if (isset($this->additionalFeaturesCache[$word]))
            return null;
        if (mb_strlen($word) <= 0)
            return null;
        $this->additionalFeaturesCache[$word] = true;
        $keyWordCount = $this->getIndexServerClient()->GetDocumentsCount("$type:\"$word\"");
        if ($keyWordCount <= $minSupportCount || ($docCount - $keyWordCount) <= $minSupportCount)
            return null;
        return new BooleanNumericFeature($word);
    }

    public function getFeatureVector(int $minSupportPercent = 12): array {
        $features = [];
        foreach ($this->numericFeatures as $feature) {
            $characteristics = $this->getNumericFeaturesCharacteristics($feature);
            $features[] = new NumericFeature($feature, $characteristics);
        }
        $queue = new IndexDocumentsQueue($this->getIndexServerClient());
        $minSupportCount = $minSupportPercent / 100 * $queue->getDocCount();
        while (!$queue->isStreamFinished()) {
            $document = $queue->getNextDocument();
            foreach ($this->textFeatures as $featureOption) {
                $fieldName = $featureOption->getName();
                if (!isset($document->{$fieldName}))
                    continue;
                $documentFieldValue = $document->{$fieldName};
                $documentFieldValueWordArray = $featureOption->split($documentFieldValue);
                foreach ($documentFieldValueWordArray as $word) {
                    $word = $featureOption->normalize($word);
                    $feature = $this->getFeatureFromWord($word, $minSupportCount, $queue->getDocCount(), $featureOption->getName());
                    if ($feature instanceof BooleanNumericFeature)
                        $features[] = $feature;
                }
            }
        }
        return $features;
    }
}