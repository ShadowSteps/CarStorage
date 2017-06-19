<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 6/19/2017
 * Time: 11:28 AM
 */

namespace Shadows\CarStorage\Core\Index;


class DocumentsQueue
{
    /**
     * @var SolrClient
     */
    private $solrClient;
    /**
     * @var \stdClass[]
     */
    private $queue = [];
    /**
     * @var int
     */
    private $docCount;
    /**
     * @var int
     */
    private $docsRead = 0;
    /**
     * @var int
     */
    private $step = 100;
    /**
     * @var int
     */
    private $stepNumber = 0;
    private $query;
    private $order;
    /**
     * DocumentsQueue constructor.
     * @param $solrClient
     */
    public function __construct(SolrClient $solrClient, string $query = "*:*", string $order = "id asc")
    {
        $this->solrClient = $solrClient;
        $this->query = $query;
        $this->order = $order;
        $this->docCount = $solrClient->GetDocumentsCount($query);
    }

    /**
     * @param int $step
     */
    public function setStep(int $step)
    {
        $this->step = $step;
    }

    public function getNextDocument(): \stdClass {
        if (count($this->queue) == 0) {
            if ($this->isStreamFinished())
                return null;
            $this->queue = $this->solrClient->Select($this->query, $this->stepNumber * $this->step, $this->step, $this->order);
            if (count($this->queue) == 0)
                return null;
            $this->stepNumber++;
        }
        $this->docsRead++;
        return array_pop($this->queue);
    }

    /**
     * @return int
     */
    public function getDocCount(): int
    {
        return $this->docCount;
    }

    public function isStreamFinished(): bool {
        return $this->docsRead >= $this->docCount;
    }
}