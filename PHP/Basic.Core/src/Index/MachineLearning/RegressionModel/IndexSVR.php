<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/5/2017
 * Time: 10:30 PM
 */

namespace Shadows\CarStorage\Core\ML\RegressionModel;

use AdSearchEngine\Code\Index\IndexDocumentsQueue;
use AdSearchEngine\Core\Index\MachineLearning\RegressionModel\AIndexRegressionModel;
use AdSearchEngine\Core\Index\MachineLearning\Utils\DocumentConvertHelper;
use Phpml\SupportVectorMachine\DataTransformer;
use Phpml\SupportVectorMachine\Kernel;
use Phpml\SupportVectorMachine\Type;

class IndexSVR extends AIndexRegressionModel
{
    private $type;
    private $kernel;
    private $cost;
    private $nu;
    private $degree;
    private $gamma;
    private $coef0;
    private $epsilon;
    private $tolerance;
    private $cacheSize;
    private $shrinking;
    private $probabilityEstimates;
    private $binPath;
    private $varPath;
    private $model;
    private $labels;

    public function __construct(
        IndexDocumentsQueue $queue,
        array $features,
        string $target,
        int $kernel = Kernel::RBF, float $cost = 1.0, int $degree = 3,
        float $gamma = null, float $coef0 = 0.0, float $epsilon = 0.1, float $tolerance = 0.001,
        int $cacheSize = 100, bool $shrinking = true
    ) {
        parent::__construct($queue, $features, $target);
        $this->type = Type::EPSILON_SVR;
        $this->kernel = $kernel;
        $this->cost = $cost;
        $this->nu = 0.5;
        $this->degree = $degree;
        $this->gamma = $gamma;
        $this->coef0 = $coef0;
        $this->epsilon = $epsilon;
        $this->tolerance = $tolerance;
        $this->cacheSize = $cacheSize;
        $this->shrinking = $shrinking;
        $this->probabilityEstimates = false;

        $rootPath = realpath(__DIR__).DIRECTORY_SEPARATOR;

        $this->binPath = $rootPath.'bin'.DIRECTORY_SEPARATOR.'libsvm'.DIRECTORY_SEPARATOR;
        $this->varPath = $rootPath.'var'.DIRECTORY_SEPARATOR;
        if (!file_exists($this->varPath))
            mkdir($this->varPath);
    }

    public function getModel()
    {
        return $this->model;
    }

    private function getOSExtension()
    {
        $os = strtoupper(substr(PHP_OS, 0, 3));
        if ($os === 'WIN') {
            return '.exe';
        } elseif ($os === 'DAR') {
            return '-osx';
        }

        return '';
    }

    private function buildTrainCommand(string $trainingSetFileName, string $modelFileName): string
    {
        return sprintf('%ssvm-train%s -s %s -t %s -c %s -n %s -d %s%s -r %s -p %s -m %s -e %s -h %d -b %d %s %s',
            $this->binPath,
            $this->getOSExtension(),
            $this->type,
            $this->kernel,
            $this->cost,
            $this->nu,
            $this->degree,
            $this->gamma !== null ? ' -g '.$this->gamma : '',
            $this->coef0,
            $this->epsilon,
            $this->cacheSize,
            $this->tolerance,
            $this->shrinking,
            $this->probabilityEstimates,
            escapeshellarg($trainingSetFileName),
            escapeshellarg($modelFileName)
        );
    }

    public function train(float $trainPercent = 1): array
    {
        $trainingSetFileName = $this->varPath.uniqid('phpml', true);
        $labels = [];
        $samples = [];
        $batchSize = 1000;
        $count = $this->getIndexDocumentsQueue()->getDocCount();
        $maxDocument = (int)round($count * $trainPercent);
        $this->getIndexDocumentsQueue()->reset();
        $docRead = 0;
        while (!$this->getIndexDocumentsQueue()->isStreamFinished() && $docRead < $maxDocument) {
            $document = $this->getIndexDocumentsQueue()->getNextDocument();
            list($convertedDocument, $target) = $this->getDocumentHelper()->convertDocumentForRegression($document, $this->getTargetFeature());
            if (!count($convertedDocument))
                continue;
            $docRead++;
            $samples[] = $convertedDocument;
            $labels[] = $target[0];
            if ($batchSize == count($samples) || $this->getIndexDocumentsQueue()->isStreamFinished() || $docRead >= $maxDocument) {
                $trainingSet = DataTransformer::trainingSet($samples, $labels, in_array($this->type, [Type::EPSILON_SVR, Type::NU_SVR]));
                if (file_exists($trainingSetFileName))
                    file_put_contents($trainingSetFileName, $trainingSet, FILE_APPEND);
                else
                    file_put_contents($trainingSetFileName, $trainingSet);
                $samples = [];
                $labels = [];
            }
        }
        $modelFileName = $trainingSetFileName.'-model';
        $command = $this->buildTrainCommand($trainingSetFileName, $modelFileName);
        $output = '';
        exec(escapeshellcmd($command), $output);
        $this->model = file_get_contents($modelFileName);
        unlink($trainingSetFileName);
        unlink($modelFileName);
        return [null];
    }

    public function predict(array $sample): array
    {
        $predicted = [];
        $features = $this->getDocumentHelper()->getFeatures();
        foreach ($features as $key => $feature) {
            if ($feature->getName() == $this->getTargetFeature()) {
                unset($features[$key]);
                break;
            }
        }
        $documentHelper = new DocumentConvertHelper($features);
        foreach ($sample as $documentSample) {
            $convertedDocument = $documentHelper->convertDocumentForClustering($documentSample);
            $y = null;
            if (count($convertedDocument)) {
                $testSet = DataTransformer::testSet([$convertedDocument]);
                file_put_contents($testSetFileName = $this->varPath.uniqid('phpml', true), $testSet);
                file_put_contents($modelFileName = $testSetFileName.'-model', $this->model);
                $outputFileName = $testSetFileName.'-output';
                $command = sprintf('%ssvm-predict%s %s %s %s', $this->binPath, $this->getOSExtension(), $testSetFileName, $modelFileName, $outputFileName);
                $output = '';
                exec(escapeshellcmd($command), $output);
                $predictions = file_get_contents($outputFileName);
                unlink($testSetFileName);
                unlink($modelFileName);
                unlink($outputFileName);
                if (in_array($this->type, [Type::C_SVC, Type::NU_SVC])) {
                    $predictions = DataTransformer::predictions($predictions, $this->labels);
                } else {
                    $predictions = explode(PHP_EOL, trim($predictions));
                }
                $y = $predictions[0];
            }
            $predicted[] = $y;
        }
        return $predicted;
    }
}