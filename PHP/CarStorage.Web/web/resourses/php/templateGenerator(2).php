<?php
$time_start = microtime(true);

$fields = ["title", "keywords","description"];
$words = 2;
$basicCombinations = [];

foreach ($fields as $field)
    for ($i = 1;  $i <= $words; $i++) {
        $basicCombinations[] = [
            "query" => "($field:word$i)",
            "words" => ["word$i" => true]
        ];
    }
$prevCombinations = $basicCombinations;
$query = "";
foreach ($prevCombinations as $prev)
    $query .= "||". $prev["query"];
$query = ltrim($query, "||");
$counts = array_fill(0, count($prevCombinations), 0);
if($words >= 2) {
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

if($words >= 3) {
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
    $prevCombinations = $newCombinations;
}

$time_end = microtime(true);
$time = $time_end - $time_start;
echo "Script took $time seconds\n";
//echo mb_strlen($query).PHP_EOL;
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
echo determineWeights($query);