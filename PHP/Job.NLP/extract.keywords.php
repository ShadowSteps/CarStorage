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
$dictionaryCachePath = __DIR__. "/res/dictionary/10kDict.cache";
$trigramCachePath = __DIR__. "/res/dictionary/Trigrams.cache";
$syntaxAnalyzerCachePath = __DIR__. "/res/dictionary/SyntaxRules.cache";
$dictionaryPath = __DIR__. "/res/dictionary/10kDict.txd";
if (file_exists($dictionaryCachePath)) {
    $dictionary = unserialize(file_get_contents($dictionaryCachePath));
} else {
    $dictionary = DictionaryReader::ReadDictionaryFromFile(__DIR__. "/res/dictionary/10kDict.txd");
    file_put_contents($dictionaryCachePath, serialize($dictionary));
}
if (file_exists($trigramCachePath)) {
    $trigramDictionary = unserialize(file_get_contents($trigramCachePath));
} else {
    $trigramDictionary = $dictionary->buildTrigramDictionary();
    file_put_contents($trigramCachePath, serialize($trigramDictionary));
}
if (file_exists($syntaxAnalyzerCachePath)) {
    $syntaxAnalyzer = unserialize(file_get_contents($syntaxAnalyzerCachePath));
} else {
    $syntaxAnalyzer = new \Shadows\CarStorage\NLP\NLP\Syntax\SyntaxAnalyzer();
//PRONOUN
    $syntaxAnalyzer->addRule(new SyntaxRule("{SE:PRO}", SyntaxGroupType::PronounGroup));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:PRON}+{SG:PRON}", SyntaxGroupType::PronounGroup));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:PRON}+{SG:PARTICLE}", SyntaxGroupType::PronounGroup));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:PARTICLE}+{SG:PRON}", SyntaxGroupType::PronounGroup));
//PARTICLE
    $syntaxAnalyzer->addRule(new SyntaxRule("{SE:PC}", SyntaxGroupType::ComplexParticle));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:PARTICLE}+{SG:PARTICLE}", SyntaxGroupType::ComplexParticle));
//VERB
    $syntaxAnalyzer->addRule(new SyntaxRule("{SE:V}", SyntaxGroupType::ComplexVerb));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:VC}+{SG:PRON}", SyntaxGroupType::ComplexVerb));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:ADVERB}+{SG:VC}", SyntaxGroupType::ComplexVerb));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:VC}+{SG:PP}", SyntaxGroupType::ComplexVerb));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:PRON}+{SG:VC}", SyntaxGroupType::ComplexVerb));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:VC}+{SG:PARTICLE}", SyntaxGroupType::ComplexVerb));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:PARTICLE}+{SG:VC}", SyntaxGroupType::ComplexVerb));
//NOUN
    $syntaxAnalyzer->addRule(new SyntaxRule("{SE:N}", SyntaxGroupType::NounPhrase));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:NP}+{SG:NP}", SyntaxGroupType::NounPhrase));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:NP}+{SG:PRON}", SyntaxGroupType::NounPhrase));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:ADJ}+{SG:NP}", SyntaxGroupType::NounPhrase));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:PARTICLE}+{SG:NP}", SyntaxGroupType::NounPhrase));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:NP}+{SG:PARTICLE}", SyntaxGroupType::NounPhrase));
//ADJECTIVE
    $syntaxAnalyzer->addRule(new SyntaxRule("{SE:A}", SyntaxGroupType::ComplexAdjective));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:ADJ}+{SG:ADJ}", SyntaxGroupType::ComplexAdjective));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:ADJ}+{SE:CONJ}+{SG:ADJ}", SyntaxGroupType::ComplexAdjective));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:ADJ}+{SG:PRON}", SyntaxGroupType::ComplexAdjective));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:ADJ}+{SG:PARTICLE}", SyntaxGroupType::ComplexAdjective));
//NUMERAL
    $syntaxAnalyzer->addRule(new SyntaxRule("{SE:NU}", SyntaxGroupType::ComplexNumeral));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:M}+{SG:M}", SyntaxGroupType::ComplexNumeral));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:M}+{SG:PRON}", SyntaxGroupType::ComplexNumeral));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:M}+{SG:PARTICLE}", SyntaxGroupType::ComplexNumeral));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:PARTICLE}+{SG:M}", SyntaxGroupType::ComplexNumeral));
//ADVERB
    $syntaxAnalyzer->addRule(new SyntaxRule("{SE:ADV}", SyntaxGroupType::ComplexAdverb));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:ADVERB}+{SG:ADVERB}", SyntaxGroupType::ComplexAdverb));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:PARTICLE}+{SG:ADVERB}", SyntaxGroupType::ComplexAdverb));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:ADVERB}+{SG:PRON}", SyntaxGroupType::ComplexAdverb));
//PREPOSITION PHRASE
    $syntaxAnalyzer->addRule(new SyntaxRule("{SE:PREP}", SyntaxGroupType::PrepositionPhrase));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:PP}+{SG:PP}", SyntaxGroupType::PrepositionPhrase));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:PP}+{SG:PRON}", SyntaxGroupType::PrepositionPhrase));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:PP}+{SG:NP}", SyntaxGroupType::PrepositionPhrase));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:PP}+{SG:ADJ}", SyntaxGroupType::PrepositionPhrase));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:PP}+{SG:ADVERB}", SyntaxGroupType::PrepositionPhrase));
//Sentance
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:VC}+{SG:NP}", SyntaxGroupType::Sentence));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:NP}+{SG:VC}", SyntaxGroupType::Sentence));
    $syntaxAnalyzer->addRule(new SyntaxRule("{SG:VC}+{SG:PP}+{SG:NP}", SyntaxGroupType::Sentence));
    file_put_contents($syntaxAnalyzerCachePath, serialize($syntaxAnalyzer));
}
$AutoCorrect = new \Shadows\CarStorage\NLP\NLP\Autocorrect\StringAutoCorrect($trigramDictionary);
$Sentence  = new Sentence();
$sentences = $Sentence->split($inputString, Sentence::SPLIT_TRIM);
$tokenizer = new \NlpTools\Tokenizers\WhitespaceAndPunctuationTokenizer();


$punctuation = [",",".","!","?",";",":", "/"];
foreach ($sentences as $key => $sentence) {
    echo "Sentence $key: $sentence".PHP_EOL;
    $tokens = $tokenizer->tokenize($sentence);
    $tokens = array_diff($tokens, $punctuation);
    $words = [];
    foreach ($tokens as $tkey => $token) {
        $wordTypes = $dictionary->findWord(mb_strtolower($token));
        if (count($wordTypes) == 1 && $wordTypes[0]->getWordType() == \Shadows\CarStorage\NLP\NLP\Morphology\Word\WordType::Unrecognized) {
            $wordString = $AutoCorrect->findClosest($wordTypes[0]->getRawForm());
            $wordTypes = $dictionary->findWord($wordString);
        }
        echo "$tkey => $token: ";
        foreach ($wordTypes as $word)
            echo $word->getWordType() . ", ";
        ECHO PHP_EOL;
        //if (!(count($wordTypes) == 1 && $wordTypes[0]->getWordType() == \Shadows\CarStorage\NLP\NLP\Morphology\Word\WordType::Unrecognized))
            $words[] = $wordTypes;
    }

    if (count($words) > 15) {
        echo "Sentence too long!".PHP_EOL;
    } else {
        $syntaxGroups = $syntaxAnalyzer->analyze($words);
        if (count($syntaxGroups)) {
            echo "Possible groups:".PHP_EOL;
            foreach ($syntaxGroups as $group)
                echo $group.PHP_EOL;
        } else {
            echo "No possible groups!".PHP_EOL;
        }
    }
}
