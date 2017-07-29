<?php
/*$fields = array("title","keywords","description");
$words = array("word1","word2");
$allQuery = "";
/*
function combineFields($fields,$words){
    $counter = 0;
    $num = count($fields);
    $permutations = array();
    $combinedFields = array();
    $total = pow(2, $num);
    $countMax = 3;
    for ($i = 0; $i < $total; $i++) {
        $countInner = 0;
        $permutations[$i] = "";
        for ($j = 0; $j < $num; $j++) {
            if (pow(2, $j) & $i){
                $countInner++;
                if ($countInner > $countMax){
                    $permutations[$i] = "";
                    break;
                }
                $permutations[$i] .= " ".$fields[$j];
            }
        }
        if($permutations[$i]!= "") {
            $flag = true;
            if(count(explode(" ",ltrim($permutations[$i]))) > $countMax){
                $flag = false;
            }else{
                foreach($words as $wnum => $word){
                    if(substr_count(ltrim($permutations[$i]), $word)>1) {
                        $flag = false;
                    }
                }
            }
            if($flag){
                $combinedFields[$counter] = ltrim($permutations[$i]);
                $counter++;
            }
        }
    }
    return $combinedFields;
}

function determineFieldWeight($field){
    switch($field){
        case "title":
            return 6;
            break;
        case "description":
            return 2;
            break;
        case "keywords":
            return 4;
            break;
    }
}

function generateQuery($fields,$query,$text)
{
    $fieldValueSet = array();
    foreach ($fields as $fieldNum => $field) {
        foreach ($text as $termNum => $term) {
            $fieldString =  "(" . $field . ":" . $term . ")";
            $fieldValueSet[] = $fieldString;
        }
    }
    $allCombinations = combineFields($fieldValueSet,$text);
    $combinationsLength = count($allCombinations);
    foreach($allCombinations as $cnum =>$combination){
        $fields = explode(" ",$combination);
        $operation = "&&";
        $fieldsLength = count($fields);
        if(count($fields)==1) {
            $operation = "||";
            $fieldName = str_replace("(", "", explode(":", $fields[0])[0]);
            $weight = determineFieldWeight($fieldName);
            $queryPart = "";
            if ($combinationsLength - 1 > $cnum){
                $queryPart = $fields[0] . "^" . $weight . $operation;
            }else {
                $queryPart = $fields[0] . "^" . $weight;
            }
            $query .= $queryPart;
        }else{
            $weight = 0;
            $queryPart="(";
            foreach($fields as $num => $item){
                $fieldName = str_replace("(","",explode(":",$item)[0]);
                $weight = $weight+ determineFieldWeight($fieldName);
                if ($fieldsLength - 1 > $num) {
                    $queryPart .= ($item) . $operation;
                }else{
                    $queryPart .= ($item);
                }
            }
            if ($combinationsLength - 1 > $cnum) {
                $queryPart.=")^".$weight."||";
            }else{
                $queryPart.=")^".$weight."";
            }
            $query.=$queryPart;
        }
    }
    return $query;
}
$wordsArray = [];
$query = generateQuery($fields,$allQuery,$words);*/

function genCombinations($values,$count=0) {
    // Figure out how many combinations are possible:
    $permCount=pow(count($values),$count);

    // Iterate and yield:
    for($i = 0; $i < $permCount; $i++)
        yield getCombination($values, $count, $i);
}

// State-based way of generating combinations:
function getCombination($values, $count, $index) {
    $result=array();
    for($i = 0; $i < $count; $i++) {
        // Figure out where in the array to start from, given the external state and the internal loop state
        $pos = $index % count($values);

        // Append and continue
        $result[] = $values[$pos];
        $index = ($index-$pos)/count($values);;
    }
    return $result;
}

function getAllCombinations()
{
    $fields = array("title","keywords","description");
    $words = array("word1","word2");

    $fieldValueSet = array();
    foreach ($fields as $fieldNum => $field) {
        foreach ($words as $termNum => $term) {
            $fieldString =  "(" . $field . ":" . $term . ")";
            $fieldValueSet[] = $fieldString;
            echo($fieldString);
        }
    }
    $query = "";
    $generator = genCombinations($fieldValueSet,2);
    $count = 0;
    foreach ($generator as $value) {
        // Do something with the value here
        $flag = true;
        $string = "";
        $combLength = count($value);
        foreach ($value as $num => $combination) {
            if ($combLength - 1 > $num) {
                $string .= $combination . "&&";
            } else {
                $string .= $combination;
            }
        }
        foreach ($words as $num => $word) {
            if (substr_count(ltrim($string), $word) > 1) {
                $flag = false;
            }
        }

        if ($flag) {
            $query .=  "(".ltrim($string).")||";
        }
    }
    return explode("||",$query);
}

function checkForDublications($string,$arrayCopy,$fields){
    foreach($arrayCopy as $num =>$comb) {
        if($comb != $string) {
            foreach ($fields as $inum => $item) {
                $item = str_replace("((", "", $item);
                $item = str_replace("(", "", $item);
                $item = str_replace(")", "", $item);
                $item = str_replace("))", "", $item);
                $pos = strpos($string, $item);
            }
        }
    }
}
function removeDublicated($combinations)
{
    $copyOfCombinations = $combinations;
    foreach ($combinations as $num => $combToCheck) {
        $items = explode("&&", $combToCheck);
        foreach ($items as $inum => $item) {
            $item = str_replace("((", "", $item);
            $item = str_replace("(", "", $item);
            $item = str_replace(")", "", $item);
            $item = str_replace("))", "", $item);
            $items[$inum]= $item;
        }
        foreach($copyOfCombinations as $cnum =>$comb) {

            $itemsc = explode("&&", $comb);
            if($comb!=$combToCheck && $combToCheck != "" && $comb !="") {
                foreach ($itemsc as $Num => $item) {
                    $item = str_replace("((", "", $item);
                    $item = str_replace("(", "", $item);
                    $item = str_replace(")", "", $item);
                    $item = str_replace("))", "", $item);
                    $itemsc[$Num] = $item;
                }
                if (count(array_diff($items, $itemsc)) == 0) {
                    $new[] = $combToCheck;
                  //var_dump("remove-------------------------------------------------------------------" . $copyOfCombinations[$cnum]);
                    $copyOfCombinations[$cnum]="";
                }
            }
        }
    }
}

$combinations = getAllCombinations();
var_dump($combinations);
//removeDublicated($combinations);
