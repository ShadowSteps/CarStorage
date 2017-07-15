<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/15/2017
 * Time: 5:21 PM
 */

namespace AdSearchEngine\Interfaces\Index\MachineLearning;


interface IIndexClustering
{
    public function clusterIndexDocuments(): array;
}