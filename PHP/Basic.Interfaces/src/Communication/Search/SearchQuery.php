<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/22/2017
 * Time: 4:12 PM
 */

namespace AdSearchEngine\Interfaces\Communication\Search;


use AdSearchEngine\Interfaces\Communication\JSONCommunicationObject;
use AdSearchEngine\Interfaces\Communication\Search\Common\FieldOrderByCriteria;
use AdSearchEngine\Interfaces\Communication\Search\Common\FieldRangeCriteria;
use AdSearchEngine\Interfaces\Communication\Search\Common\FieldSearchCriteria;
use AdSearchEngine\Interfaces\Communication\Utils\StdClassExtractor;

class SearchQuery extends JSONCommunicationObject
{
    private $fieldsSearchCriteria = [];
    private $orderCriteria = [];
    private $fieldsRangeCriteria = [];
    private $itemsPerPage;
    private $page;

    /**
     * SearchQuery constructor.
     * @param $itemsPerPage
     * @param $page
     */
    public function __construct(int $itemsPerPage, int $page)
    {
        $this->itemsPerPage = $itemsPerPage;
        $this->page = $page;
    }


    public function addSearchCriteria(FieldSearchCriteria $criteria) {
        $this->fieldsSearchCriteria[] = $criteria;
    }

    public function addRangeCriteria(FieldRangeCriteria $criteria) {
        $this->fieldsRangeCriteria[] = $criteria;
    }

    public function addOrderByCriteria(FieldOrderByCriteria $criteria){
        $this->orderCriteria[] = $criteria;
    }

    /**
     * @return FieldSearchCriteria[]
     */
    public function getFieldsSearchCriteria(): array
    {
        return $this->fieldsSearchCriteria;
    }

    /**
     * @return FieldOrderByCriteria[]
     */
    public function getOrderCriteria(): array
    {
        return $this->orderCriteria;
    }

    /**
     * @return FieldRangeCriteria[]
     */
    public function getFieldsRangeCriteria(): array
    {
        return $this->fieldsRangeCriteria;
    }


    public static function fromSTD(\stdClass $object) {
        $extractor = new StdClassExtractor($object);
        $self = new self($extractor->GetInteger("itemsPerPage"), $extractor->GetInteger("page"));
        $searchCriteria = $object->fieldsSearchCriteria;
        foreach ($searchCriteria as $criterion) {
            $self->addSearchCriteria(FieldSearchCriteria::fromSTD($criterion));
        }
        $orderCriteria = $object->orderCriteria;
        foreach ($orderCriteria as $criterion) {
            $self->addOrderByCriteria(FieldOrderByCriteria::fromSTD($criterion));
        }
        $rangeCriteria = $object->fieldsRangeCriteria;
        foreach ($rangeCriteria as $criterion) {
            $self->addRangeCriteria(FieldRangeCriteria::fromSTD($criterion));
        }
        return $self;
    }

    /**
     * @return int
     */
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }


}