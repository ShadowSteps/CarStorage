<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 30.12.2016 г.
 * Time: 15:17
 */
require_once __DIR__ . "/vendor/autoload.php";
if ($argc < 2)
    throw new Exception("Input file with string not given!");
$inputFile = $argv[1];
if (!file_exists($inputFile))
    throw new Exception("Input file does not exist!");
echo "Input file: $inputFile".PHP_EOL;
$inputString = file_get_contents($inputFile);
echo "Input string: ". $inputString.PHP_EOL;
$dictionaryCachePath = __DIR__. "/res/dictionary/10kDict.cache";
$dictionaryPath = __DIR__. "/res/dictionary/10kDict.txd";
if (file_exists($dictionaryCachePath)) {
    $dictionary = unserialize(file_get_contents($dictionaryCachePath));
} else {
    $dictionary = \Shadows\CarStorage\NLP\NLP\Morphology\DictionaryReader::ReadDictionaryFromFile(__DIR__. "/res/dictionary/10kDict.txd");
    file_put_contents($dictionaryCachePath, serialize($dictionary));
}
$Sentence  = new Sentence;
$sentences = $Sentence->split($inputString, Sentence::SPLIT_TRIM);
$tokenizer = new \NlpTools\Tokenizers\WhitespaceAndPunctuationTokenizer();
$punctuation = [",",".","!","?",";",":"];
foreach ($sentences as $key => $sentence) {
    echo "Sentence $key: $sentence".PHP_EOL;
    $tokens = $tokenizer->tokenize($sentence);
    $tokens = array_diff($tokens, $punctuation);
    foreach ($tokens as $tkey => $token) {
        $word = $dictionary->findWord(mb_strtolower($token));
        echo "$tkey => $token: ".$word->getWordType().PHP_EOL;
    }

}
