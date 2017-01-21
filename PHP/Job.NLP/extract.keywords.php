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
$bigramCachePath = __DIR__. "/res/dictionary/Bigrams.cache";
$dictionaryPath = __DIR__. "/res/dictionary/10kDict.txd";
if (file_exists($dictionaryCachePath)) {
    $dictionary = unserialize(file_get_contents($dictionaryCachePath));
} else {
    $dictionary = DictionaryReader::ReadDictionaryFromFile(__DIR__. "/res/dictionary/10kDict.txd");
    file_put_contents($dictionaryCachePath, serialize($dictionary));
}
if (file_exists($bigramCachePath)) {
    $bigramDictionary = unserialize(file_get_contents($bigramCachePath));
} else {
    $bigramDictionary = $dictionary->buildBigramDictionary();
    file_put_contents($bigramCachePath, serialize($bigramDictionary));
}
$AutoCorrect = new \Shadows\CarStorage\NLP\NLP\Autocorrect\StringAutoCorrect($bigramDictionary);
$Sentence  = new Sentence();
$sentences = $Sentence->split($inputString, Sentence::SPLIT_TRIM);
$tokenizer = new \NlpTools\Tokenizers\WhitespaceAndPunctuationTokenizer();
$syntaxAnalyzer = new \Shadows\CarStorage\NLP\NLP\Syntax\SyntaxAnalyzer();
$syntaxAnalyzer->addRule(new SyntaxRule("{SG:NP}+{SG:VP}", SyntaxGroupType::Sentence));
$syntaxAnalyzer->addRule(new SyntaxRule("{SE:N}", SyntaxGroupType::NounPhrase));
$syntaxAnalyzer->addRule(new SyntaxRule("{SE:A}+{SE:N}", SyntaxGroupType::NounPhrase));
$syntaxAnalyzer->addRule(new SyntaxRule("{SE:PREP}+{SE:ADV}+{SG:NP}", SyntaxGroupType::NounPhrase));
$syntaxAnalyzer->addRule(new SyntaxRule("{SE:A}+{SE:CONJ}+{SG:NP}", SyntaxGroupType::NounPhrase));
$syntaxAnalyzer->addRule(new SyntaxRule("{SE:V}+{SG:VP}", SyntaxGroupType::VerbPhrase));
$syntaxAnalyzer->addRule(new SyntaxRule("{SE:V}+{SG:NP}", SyntaxGroupType::VerbPhrase));
$punctuation = [",",".","!","?",";",":"];
foreach ($sentences as $key => $sentence) {
    echo "Sentence $key: $sentence".PHP_EOL;
    $tokens = $tokenizer->tokenize($sentence);
    $tokens = array_diff($tokens, $punctuation);
    $words = [];
    foreach ($tokens as $tkey => $token) {
        $word = $dictionary->findWord(mb_strtolower($token));
        if ($word->getWordType() == 'U')
        {
            $wordString = $AutoCorrect->findClosest($word->getRawForm());
            $word = $dictionary->findWord($wordString);
        }
        echo "$tkey => $token: ".$word->getWordType().PHP_EOL;
        $words[] = $word;
    }
    $syntaxGroups = $syntaxAnalyzer->analyze($words);
    if (count($syntaxGroups)) {
        echo "Possible groups:".PHP_EOL;
        foreach ($syntaxGroups as $group)
            echo $group.PHP_EOL;
    } else {
        echo "No possible groups!".PHP_EOL;
    }
}
