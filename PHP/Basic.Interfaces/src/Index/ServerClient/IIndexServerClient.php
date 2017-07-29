<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/15/2017
 * Time: 1:22 PM
 */

namespace AdSearchEngine\Interfaces\Index\ServerClient;

use AdSearchEngine\Interfaces\Communication\Search\SearchQuery;
use AdSearchEngine\Interfaces\Index\AdIndexInformation;

interface IIndexServerClient
{
    public function AddFileToIndex(AdIndexInformation $information): void;
    /**
     * @param AdIndexInformation[] $documents
     */
    public function UpdateDocumentArray(array $documents): void;
    public function UpdateDocumentField(string $documentId, string $fieldName, $value): void;
    public function UpdateDocumentArrayField(string $fieldName, array $values): void;
    public function GetDocumentsCount(string $query = "*:*"): int;
    public function Select(string $query, int $start, int $count, string $sort = null): array;
    public function DeleteById(string $documentId): void;
    public function SelectDocumentById(string $documentId): \stdClass;
    public function GetMaxOfNumericFeature(string $feature): float;
    public function GetMinOfNumericFeature(string $feature): float;
    public function GetAverageOfNumericFeature(string $feature, string $query = "*:*"): float;
    public function GetSigmaDispersionOfNumericFeature(string $feature): float;
    public function GetMedianOfNumericFeature(string $feature, string $query = "*:*"): float;
    public function GetFirstQuartileOfNumericFeature(string $feature, string $query = "*:*"): float;
    public function GetThirdQuartileOfNumericFeature(string $feature, string $query = "*:*"): float;
    public function Search(SearchQuery $query): array;
}