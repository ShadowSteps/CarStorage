<?php
namespace AppBundle;
/**
 * Created by PhpStorm.
 * User: Misho
 * Date: 28.12.2016 г.
 * Time: 12:54
 */
class queryGenerator
{
    private $options;
    private $fields = array();
    private $baseUrl;
    private $query;
    private $page;


    private $itemsPerPage;

    public function __construct($url,$options){
        $this->setOptions($options);
        $this->setBaseUrl($url);

        $this->fields = array();
    }

    /**
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param mixed $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param mixed $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param mixed $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    private function getText(){
        return $this->getOptions()['text'];
    }

    private function getAllField(){
        return $this->getOptions()['all'];
    }

    private function getDistance(){
        return $this->getOptions()['distance'];
    }

    private function getYear(){
        return $this->getOptions()['year'];
    }

    private function getDescription(){
        return $this->getOptions()['description'];
    }

    private function getKeywords(){
        return $this->getOptions()['keywords'];
    }

    private function gettTitle(){
        return $this->getOptions()['title'];
    }

    private function getPrice(){
        return $this->getOptions()['price'];
    }

    private function getHighlight(){
        return $this->getOptions()['highlight'];
    }

    private function getPage(){
        return $this->getOptions()['page'];
    }

    private function getItemsPerPage(){
        return $this->getOptions()['itemsPerPage'];
    }

    public function prepareResultsForVisualization($results){
        foreach($results->body->response->docs as $num => $item){
            $pieces = explode(" ",$item->description[0]);
            if(str_word_count($item->description[0])>0) {
                $description = implode(" ", array_splice($pieces, 0, 70)) . "...";
            }else{
                $description = "Няма налично описание.";
            }
            $item->description = $description;
            $item->keywords = array_slice($item->keywords, 0, 15);
            $results->body->response->docs[$num] = $item;
        }
        $result = $results;
        return $result;
    }

    public function getFieldsForSearch(){
        $fields = array();
            if($this->getAllField() == "true")
                return array( 0 => "title",1=>"keywords",2=>"description");
            if($this->getDescription() == "true")
                $fields[] = "description";
            if($this->getKeywords() == "true")
                $fields[] = "keywords";
            if($this->gettTitle() == "true")
                $fields[] = "title";
        return $fields;
    }

    public function generateHighlightQuery(){
        $highlightString="";
        if($this->getHighlight()=="true")
        {
            $highlightString .="&hl=on&hl.fl=";
            $fields = $this->getFieldsForSearch();
            foreach ($fields as $num => $field) {
                $highlightString.= $num==0 ? $field : ",".$field;
            }
        }
        return $highlightString;
    }

    public function buildQueryString(){
        $rangeQueryPart = "(".$this->getPrice().")&&(".$this->getDistance().")&&(".$this->getYear().")";
        if($this->getText()!="*:*") {
            $query = "(".$this->generateQuery(explode(" ", $this->getText()), $this->getFieldsForSearch()).")&&".$rangeQueryPart;
        }else{
            $query = "(".$this->getText().") && ".$rangeQueryPart;
        }
        $this->setQuery($query);
    }

    public function parseQuery(){
        $this->buildQueryString();
    }

    public function performQuery(){
        $this->parseQuery();
        $from = ($this->getPage()-1)*$this->getItemsPerPage();
        $headers = array(
            'Content-Type' => 'application/json'
        );
        $data = array(
            "query" => $this->getQuery(),
            "offset" =>$from,
            "limit" => $this->getItemsPerPage()
            );
        $body = \Unirest\Request\Body::json($data);
        $highlight = $this->generateHighlightQuery();
        $response = \Unirest\Request::post("http://localhost:8983/solr/carstorage/select?wt=json".$highlight, $headers, $body);
        return $response;
    }

    public function determineFieldWeight($field){
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
                $weight = $weight + $this->determineFieldWeight(explode(":",$item)[0]);
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

    public function generateQuery($text,$fields){
        $words = count($text);
        $basicCombinations = [];
        foreach ($fields as $field)
            for ($i = 1;  $i <= $words; $i++) {
                $basicCombinations[] = [
                    "query" => "($field:".$text[$i-1].")",
                    "words" => [$text[($i-1)] => true]
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
        return rtrim($this->determineWeights($query),"||");
    }
}
