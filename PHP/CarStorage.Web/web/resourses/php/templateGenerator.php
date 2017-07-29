<?php
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

function getAllCombinations($words)
{
    $fields = array("keywords","description");
    //$words = array("word1","word2","word3","word4","word5","word6","word7","word8");

    $fieldValueSet = array();
    foreach ($fields as $fieldNum => $field) {
        foreach ($words as $termNum => $term) {
            $fieldString =  "(" . $field . ":" . $term . ")";
            $fieldValueSet[] = $fieldString;
        }
    }
    $query = "";
    $wordCount = count($words);
    for($i=1;$i<=($wordCount >= 2 ? 2 : $wordCount);$i++) {
        $generator = genCombinations($fieldValueSet,$i);
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
    $new = [];
    foreach ($combinations as $num => $combToCheck) {
        $items = explode("&&", $combToCheck);
        foreach ($items as $inum => $item) {
            $item = str_replace("((", "", $item);
            $item = str_replace("(", "", $item);
            $item = str_replace(")", "", $item);
            $item = str_replace("))", "", $item);
            $items[$inum]= $item;
        }
        foreach($new as $cnum =>$comb) {
            $itemsc = explode("&&", $comb);
            foreach ($itemsc as $Num => $item) {
                $item = str_replace("((", "", $item);
                $item = str_replace("(", "", $item);
                $item = str_replace(")", "", $item);
                $item = str_replace("))", "", $item);
                $itemsc[$Num] = $item;
            }
            if (count(array_diff($items, $itemsc)) == 0) {
                continue 2;
            }
        }
        $new[] = $combToCheck;
    }
    return $new;
}
function determineWeights($query){
    $finalQuery = "";
    $arr = explode("||",$query);
    $lengthA = count($arr);
    foreach ($arr as $num => $combToCheck) {
        $items = explode("&&", $combToCheck);
        $weight = 0;
        $substring = "";
        $length = count($items);
        foreach ($items as $inum => $item) {
            $item = str_replace("((", "", $item);
            $item = str_replace("(", "", $item);
            $item = str_replace(")", "", $item);
            $item = str_replace("))", "", $item);
            $weight = $weight + determineFieldWeight(explode(":",$item)[0]);
            if($length-1 > $inum) {
                $substring .= "(" . $item . ")&&";
            }else{
                $substring .= "(" . $item . ")";
            }
        }
        if(count($items) == 1){
            $finalQuery .="(".$item.")^".$weight."||";
        }else{
            if($lengthA-1 > $num) {
                $finalQuery .= "(" . $substring . ")^" . $weight . "||";
            }else{
                $finalQuery .= "(" . $substring . ")^" . $weight;
            }
        }
    }
    return $finalQuery;
}
$words = array();
for($i = 1; $i<= 12;$i++){
    $words[] = "word".$i;
    $combinations = getAllCombinations($words);
    $combinations = removeDublicated($combinations);
    array_pop($combinations);
    $query = implode("||", $combinations);
    $final = determineWeights($query);
    var_dump("ready:".$i);
    file_put_contents("template_k&&d_".$i.".txt", $final);
}
