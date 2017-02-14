<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 30.12.2016 г.
 * Time: 15:17
 */
require_once __DIR__ . "/vendor/autoload.php";

use Shadows\CarStorage\NLP\NLP\Morphology\DictionaryReader;
use Shadows\CarStorage\NLP\NLP\Syntax\Group\SyntaxGroupType;
use Shadows\CarStorage\NLP\NLP\Syntax\SyntaxRule;

if ($argc < 2)
    throw new Exception("Input file with string not given!");
$inputFile = $argv[1];
if (!file_exists($inputFile))
    throw new Exception("Input file does not exist!");
echo "Input file: $inputFile".PHP_EOL;
$inputString = file_get_contents($inputFile);
echo "Input string: ". $inputString.PHP_EOL;
$rules = [
    new SyntaxRule("{SE:PRO}", SyntaxGroupType::PronounGroup),
    new SyntaxRule("{SE:PC}", SyntaxGroupType::ComplexParticle),
    new SyntaxRule("{SG:PARTICLE}+{SG:PARTICLE}", SyntaxGroupType::ComplexParticle),
    new SyntaxRule("{SE:V}", SyntaxGroupType::ComplexVerb),
    new SyntaxRule("{SE:V}+{SG:PRON}", SyntaxGroupType::ComplexVerb),
    new SyntaxRule("{SG:VC}+{SE:CONJ}+{SG:VC}", SyntaxGroupType::ComplexVerb),
    new SyntaxRule("{SG:ADVERB}+{SG:VC}", SyntaxGroupType::ComplexVerb),
    new SyntaxRule("{SG:VC}+{SG:PP}", SyntaxGroupType::ComplexVerb),
    new SyntaxRule("{SG:PRON}+{SG:VC}", SyntaxGroupType::ComplexVerb),
    new SyntaxRule("{SG:VC}+{SG:PARTICLE}", SyntaxGroupType::ComplexVerb),
    new SyntaxRule("{SG:PARTICLE}+{SG:VC}", SyntaxGroupType::ComplexVerb),
    new SyntaxRule("{SE:N}", SyntaxGroupType::NounPhrase),
    new SyntaxRule("{SG:NP}+{SE:CONJ}+{SG:NP}", SyntaxGroupType::NounPhrase),
    new SyntaxRule("{SG:NP}+{SG:PRON}", SyntaxGroupType::NounPhrase),
    new SyntaxRule("{SG:M}+{SG:NP}", SyntaxGroupType::NounPhrase),
    new SyntaxRule("{SG:PRON}+{SG:NP}", SyntaxGroupType::NounPhrase),
    new SyntaxRule("{SG:ADJ}+{SG:NP}", SyntaxGroupType::NounPhrase),
    new SyntaxRule("{SE:V}+{SG:NP}", SyntaxGroupType::NounPhrase),
    new SyntaxRule("{SG:NP}+{SG:ADJ}", SyntaxGroupType::NounPhrase),
    new SyntaxRule("{SG:PARTICLE}+{SG:NP}", SyntaxGroupType::NounPhrase),
    new SyntaxRule("{SG:NP}+{SG:PARTICLE}", SyntaxGroupType::NounPhrase),
    new SyntaxRule("{SE:A}", SyntaxGroupType::ComplexAdjective),
    new SyntaxRule("{SG:ADJ}+{SG:ADJ}", SyntaxGroupType::ComplexAdjective),
    new SyntaxRule("{SG:ADJ}+{SE:CONJ}+{SG:ADJ}", SyntaxGroupType::ComplexAdjective),
    new SyntaxRule("{SG:ADJ}+{SG:PARTICLE}", SyntaxGroupType::ComplexAdjective),
    new SyntaxRule("{SG:PARTICLE}+{SG:ADJ}", SyntaxGroupType::ComplexAdjective),
    new SyntaxRule("{SE:NU}", SyntaxGroupType::ComplexNumeral),
    new SyntaxRule("{SG:M}+{SG:M}", SyntaxGroupType::ComplexNumeral),
    new SyntaxRule("{SG:M}+{SG:PARTICLE}", SyntaxGroupType::ComplexNumeral),
    new SyntaxRule("{SG:PARTICLE}+{SG:M}", SyntaxGroupType::ComplexNumeral),
    new SyntaxRule("{SE:ADV}", SyntaxGroupType::ComplexAdverb),
    new SyntaxRule("{SG:ADVERB}+{SE:CONJ}+{SG:ADVERB}", SyntaxGroupType::ComplexAdverb),
    new SyntaxRule("{SG:PARTICLE}+{SG:ADVERB}", SyntaxGroupType::ComplexAdverb),
    new SyntaxRule("{SG:ADVERB}+{SG:PARTICLE}", SyntaxGroupType::ComplexAdverb),
    new SyntaxRule("{SE:PREP}", SyntaxGroupType::PrepositionPhrase),
    new SyntaxRule("{SG:PP}+{SG:PP}", SyntaxGroupType::PrepositionPhrase),
    new SyntaxRule("{SG:PP}+{SG:PRON}", SyntaxGroupType::PrepositionPhrase),
    new SyntaxRule("{SG:PP}+{SG:NP}", SyntaxGroupType::PrepositionPhrase),
    new SyntaxRule("{SG:PP}+{SG:ADJ}", SyntaxGroupType::PrepositionPhrase),
    new SyntaxRule("{SG:PP}+{SG:ADVERB}", SyntaxGroupType::PrepositionPhrase),
    new SyntaxRule("{SG:VC}+{SG:NP}", SyntaxGroupType::Sentence),
    new SyntaxRule("{SG:NP}+{SG:VC}", SyntaxGroupType::Sentence),
    new SyntaxRule("{SG:VC}+{SG:PP}", SyntaxGroupType::Sentence),
    new SyntaxRule("{SG:PP}+{SG:VC}", SyntaxGroupType::Sentence),
    new SyntaxRule("{SG:VC}", SyntaxGroupType::Sentence),
    new SyntaxRule("{SG:NP}", SyntaxGroupType::Sentence)
];
$schemes = [
    '0\[S 1\[VC 2\[V (има|притежава)]2]1 1\[(PP|NP) 2\[(?P<keyword>.*)\]2\]1\]0',
    '0\[S 1\[NP (?P<keyword>.*?)\]1\]0',
    '0\[S .*?[0-9]+\[[A-Z]+ (?P<level>[0-9]+)\[VC [0-9]+\[V (е|има|притежава)\][0-9]+\](\k<level>) (\k<level>)\[(PP|NP) (?P<keyword>.*)\](\k<level>).*\]0'
];
$extractor = new \Shadows\CarStorage\NLP\NLP\Keywords\Extractor($rules);

/*
for ($i = 0; $i <= 10; $i ++) {
    $requestUrl = "http://localhost:8983/solr/car_storage_v3/select?indent=on&q=*:*&wt=json&rows=10&start=" . $i*10;
    $response = \Unirest\Request::get($requestUrl);
    $responseJSON = json_decode($response->raw_body);
    foreach ($responseJSON->response->docs as $doc) {
        $text = $doc->description;
        echo $text . PHP_EOL;
        print_r($extractor->getKeywordsForString($text, $schemes));
    }
}*/

print_r($extractor->getKeywordsForString($inputString, $schemes));
