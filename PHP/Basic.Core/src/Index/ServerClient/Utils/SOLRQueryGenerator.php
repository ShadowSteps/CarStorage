<?php
/**
 * Created by PhpStorm.
 * User: kiril
 * Date: 7/22/2017
 * Time: 5:03 PM
 */

namespace AdSearchEngine\Core\Index\ServerClient\Utils;


use AdSearchEngine\Interfaces\Communication\Search\SearchQuery;

class SOLRQueryGenerator
{

    public function generateQuery(SearchQuery $query) {
        $basicCombinations = [];
        $uniqueWords = [];
        foreach ($query->getFieldsSearchCriteria() as $criteria) {
            $textEntries = explode(" ", $criteria->getSearchTerm());
            $words = count($textEntries);
            for ($i = 1;  $i <= $words; $i++) {
                if (!isset($uniqueWords[$textEntries[$i-1]]))
                    $uniqueWords[$textEntries[$i-1]] = true;
                $basicCombinations[] = [
                    "query" => "({$criteria->getFieldName()}:".$textEntries[$i-1].")^".$criteria->getFieldWeight(),
                    "words" => [$textEntries[$i-1] => true]
                ];
            }
        }
        $prevCombinations = $basicCombinations;
        $query = "";
        foreach ($prevCombinations as $prev)
            $query .= "||". $prev["query"];
        $query = ltrim($query, "||");
        $counts = array_fill(0, count($prevCombinations), 0);
        if(count($uniqueWords) >= 2) {
            $newCombinations = [];
            foreach ($prevCombinations as $key => $prev) {
                for ($j = $key + 1; $j < count($basicCombinations); $j++) {
                    $basicComb = $basicCombinations[$j];
                    $word = array_keys($basicComb["words"])[0];
                    if (isset($prev["words"][$word]))
                        continue;
                    $new = $prev;
                    $new["query"] = $prev["query"] . "&&" . $basicComb["query"];
                    $new["words"][$word] = true;
                    $newCombinations[] = $new;
                    $counts[$key]++;
                }
            }
            foreach ($newCombinations as $new)
                $query .= "||(". $new["query"].")";
            $prevCombinations = $newCombinations;
        }

        if(count($uniqueWords) >= 3) {
            $newCombinations = [];
            foreach ($counts as $key => $count) {
                if ($count == 0)
                    continue;
                for ($i = 0; $i < $count; $i++) {
                    $prev = array_shift($prevCombinations);
                    for ($j = $key + 2 + $i; $j < count($basicCombinations); $j++) {
                        $basicComb = $basicCombinations[$j];
                        $word = array_keys($basicComb["words"])[0];
                        if (isset($prev["words"][$word]))
                            continue;
                        $new = $prev;
                        $new["query"] = $prev["query"] . "&&" . $basicComb["query"];
                        $new["words"][$word] = true;
                        $newCombinations[] = $new;
                    }
                }
            }
            foreach ($newCombinations as $new)
                $query .= "||(". $new["query"].")";
        }
        return rtrim($query,"||");
    }
}