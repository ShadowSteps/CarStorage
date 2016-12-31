<?php
$time_start = microtime(true);

$fields = ["title", "keywords", "description"];
$words = 10;
$basicCombinations = [];

foreach ($fields as $field)
    for ($i = 1;  $i <= $words; $i++) {
        $basicCombinations[] = [
            "query" => "($field:word$i)",
            "combinations" => ["($field:word$i)" => true],
            "words" => ["word$i" => true]
        ];
    }
$prevCombinations = $basicCombinations;
$query = "";
foreach ($prevCombinations as $prev)
    $query .= "||". $prev["query"];

$query = ltrim($query, "||");
for ($i = 2; $i <= ($words < 3 ? $words : 3); $i++){
    $newCombinations = [];
    foreach ($basicCombinations as $key => $basicComb)
        for ($j = $key + 1; $j < count($prevCombinations); $j++) {
            $prev = $prevCombinations[$j];
            $word = array_keys($basicComb["words"])[0];
            if (isset($prev["words"][$word]))
                continue;
            $basicQuery = $basicComb["query"];
            $prev["query"] = $prev["query"] . "&&" . $basicQuery;
            $prev["combinations"][$basicQuery] = true;
            $prev["words"][$word] = true;
            $newCombinations[] = $prev;
        }
    foreach ($newCombinations as $new)
        $query .= "||". $new["query"];
    $prevCombinations = $newCombinations;
}

$time_end = microtime(true);
$time = $time_end - $time_start;
echo "Script took $time seconds\n";
echo mb_strlen($query).PHP_EOL;
//echo $query;