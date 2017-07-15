<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/15/2017
 * Time: 5:22 PM
 */

namespace AdSearchEngine\Interfaces\Index\MachineLearning;


interface IIndexKNNFinder
{
    public function FindKNearestNeighbours(string $documentId, int $k): array;
}