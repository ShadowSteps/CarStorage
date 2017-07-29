<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/22/2017
 * Time: 4:11 PM
 */

namespace AdSearchEngine\Interfaces\WebAPI\Controller;


use AdSearchEngine\Interfaces\Communication\Search\SearchQuery;

interface ISearchController
{
    public function searchAction(SearchQuery $query);
}