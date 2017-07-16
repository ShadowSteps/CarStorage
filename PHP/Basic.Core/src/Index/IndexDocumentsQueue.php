<?php
namespace AdSearchEngine\Core\Index;


use AdSearchEngine\Interfaces\Index\ServerClient\IIndexServerClient;

class IndexDocumentsQueue
{
    private $indexServerClient;
    /**
     * @var \stdClass[]
     */
    private $queue = [];
    private $docCount;
    private $docsRead = 0;
    private $step = 100;
    private $stepNumber = 0;
    private $query;
    private $orderByStatement;

    public function __construct(IIndexServerClient $client, string $processQuery = "*:*", string $orderBy = null)
    {
        $this->indexServerClient = $client;
        $this->query = $processQuery;
        $this->orderByStatement = $orderBy;
        $this->docCount = $this->indexServerClient->GetDocumentsCount($processQuery);
    }

    public function setStep(int $step)
    {
        $this->step = $step;
    }

    public function getNextDocument(): \stdClass {
        if (count($this->queue) == 0) {
            if ($this->isStreamFinished())
                return null;
            $this->queue = $this->indexServerClient->Select($this->query, $this->stepNumber * $this->step, $this->step, $this->orderByStatement);
            if (count($this->queue) == 0)
                return null;

            $this->stepNumber++;

        }
        $this->docsRead++;

        return array_pop($this->queue);
    }

    public function getDocCount(): int
    {
        return $this->docCount;
    }

    public function isStreamFinished(): bool {
        return $this->docsRead >= $this->docCount;
    }

    public function reset(string $order = null) {
        $this->docsRead = 0;
        $this->stepNumber = 0;
        if (!is_null($order) && mb_strlen($order) > 0)
            $this->orderByStatement = $order;
    }
}