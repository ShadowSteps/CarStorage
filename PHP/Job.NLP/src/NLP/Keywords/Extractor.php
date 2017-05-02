<?php


namespace Shadows\CarStorage\NLP\NLP\Keywords;


use NlpTools\Tokenizers\WhitespaceAndPunctuationTokenizer;
use Shadows\CarStorage\NLP\NLP\Autocorrect\StringAutoCorrect;
use Shadows\CarStorage\NLP\NLP\Morphology\DictionaryReader;
use Shadows\CarStorage\NLP\NLP\Morphology\Word\WordType;
use Shadows\CarStorage\NLP\NLP\Syntax\SyntaxAnalyzer;
use Shadows\CarStorage\Utils\Exception\XPathElementNotFoundException;
use Shadows\CarStorage\Utils\XPath\XPathHelper;

class Extractor
{
    private $syntaxAnalyzer;
    private $dictionary;
    private $autoCorrect;
    private $punctuationSymbols = [",",".","!","?",";",":", "/", ""];
    private $sentenceSplitter;
    private $tokenizer;

    public function __construct(array $syntaxRules, string $cacheDirectory = "", string $dictionaryPath = "")
    {
        if (strlen($cacheDirectory) > 0) {
            if (!file_exists($cacheDirectory)||!is_dir($cacheDirectory))
                throw new \InvalidArgumentException("Cache directory does not exist!");
        } else
            $cacheDirectory = __DIR__ . "/../../../res/cache";
        if (strlen($dictionaryPath) > 0) {
            if (!file_exists($dictionaryPath)||is_dir($dictionaryPath))
                throw new \InvalidArgumentException("Dictionary file does not exist!");
        } else
            $dictionaryPath = __DIR__ . "/../../../res/dictionary/10kDict.txd";
        $dictionaryCachePath = $cacheDirectory . "/" . md5($dictionaryPath) . ".cache";
        $trigramCachePath = $cacheDirectory . "/Trigrams.cache";
        $syntaxAnalyzerCachePath = $cacheDirectory . "/SyntaxRules.cache";
        if (file_exists($dictionaryCachePath)) {
            $this->dictionary = unserialize(file_get_contents($dictionaryCachePath));
        } else {
            $this->dictionary = DictionaryReader::ReadDictionaryFromFile($dictionaryPath);
            file_put_contents($dictionaryCachePath, serialize($this->dictionary));
        }
        if (file_exists($trigramCachePath)) {
            $trigramDictionary = unserialize(file_get_contents($trigramCachePath));
        } else {
            $trigramDictionary = $this->dictionary->buildTrigramDictionary();
            file_put_contents($trigramCachePath, serialize($trigramDictionary));
        }
        $this->autoCorrect = new StringAutoCorrect($trigramDictionary);
        if (file_exists($syntaxAnalyzerCachePath)) {
            $this->syntaxAnalyzer = unserialize(file_get_contents($syntaxAnalyzerCachePath));
        } else {
            $this->syntaxAnalyzer = new SyntaxAnalyzer();
            foreach ($syntaxRules as $rule)
                $this->syntaxAnalyzer->addRule($rule);
            file_put_contents($syntaxAnalyzerCachePath, serialize($this->syntaxAnalyzer));
        }
        $this->sentenceSplitter = new \Sentence();
        $this->tokenizer = new WhitespaceAndPunctuationTokenizer();
    }

    public function getKeywordsForString(string $text, array $schemes): array {
        $keywords = [];
        $Sentence = new \Sentence();
        file_put_contents("output.txt", PHP_EOL."----------------------TEXT----------------------".PHP_EOL, FILE_APPEND);
        file_put_contents("output.txt", $text. PHP_EOL, FILE_APPEND);
        $text = str_replace([',','-'], '.', $text);
        $text = mb_ereg_replace('\s+([\.!?])', '\\1', $text);
        $text = mb_ereg_replace('([\.!?])([^\s.?!])', '\\1 \\2', $text);
        $text = mb_ereg_replace('([\.!?])+', '\\1', $text);
        file_put_contents("output.txt", PHP_EOL."----------------------FORMATED TEXT----------------------".PHP_EOL, FILE_APPEND);
        file_put_contents("output.txt", $text. PHP_EOL, FILE_APPEND);
        $sentences = $Sentence->split($text, \Sentence::SPLIT_TRIM);
        file_put_contents("output.txt", PHP_EOL."----------------------SENTANCES----------------------".PHP_EOL, FILE_APPEND);
        foreach ($sentences as $key => $sentence)
            file_put_contents("output.txt", $key ." => " . $sentence. PHP_EOL, FILE_APPEND);
        file_put_contents("output.txt", PHP_EOL."----------------------TOKENIZATION----------------------".PHP_EOL, FILE_APPEND);
        $morphology = [];
        $syntax = [];
        $syntaxXML = [];
        foreach ($sentences as $key => $sentence) {
            $tokens = $this->tokenizer->tokenize($sentence);
            $tokens = array_diff($tokens, $this->punctuationSymbols);
            file_put_contents("output.txt", $key ." => (" . implode("; ", $tokens).")". PHP_EOL, FILE_APPEND);
            $words = [];
            foreach ($tokens as $tkey => $token) {
                $wordTypes = $this->dictionary->findWord(mb_strtolower($token));
                if (count($wordTypes) == 1 && $wordTypes[0]->getWordType() == WordType::Unrecognized) {
                    $wordString = $this->autoCorrect->findClosest($wordTypes[0]->getRawForm());
                    $wordTypes = $this->dictionary->findWord($wordString);
                }
                $words[] = $wordTypes;
            }
            if (count($words) > 10 || count($words) <= 0) {
                continue;
            } else {
                foreach ($words as $num => $word) {
                    foreach ($word as $meaning)
                        $morphology[$key][$num][] = $meaning->toString();
                }
                $undefinedCount = 0;
                foreach ($words as $word)
                    if (count($word) == 1&&$word[0]->getWordType() == WordType::Unrecognized) $undefinedCount++;
                if (($undefinedCount / count($words)) > 0.6)
                    continue;
                $syntaxGroups = $this->syntaxAnalyzer->analyze($words, 1);
                if (!count($syntaxGroups))
                    continue;
                foreach ($syntaxGroups as $group) {
                    $syntax[$key][] = $group->toString();
                    $syntaxXML[$key][] = $group->toXML();
                    $groupText = $group->toXML();
                    $document = new \DOMDocument("1.0", "UTF-8");
                    $document->loadXML($groupText);
                    $search = new \DOMXPath($document);
                    foreach ($schemes as $scheme)
                    {
                        $result = $search->query($scheme);
                        if ($result->length == 0)
                            continue;
                        $item = $result->item(0)->textContent;
                        if (!in_array($item, $keywords))
                            $keywords[] = $item;
                    }
                }
            }
        }
        file_put_contents("output.txt", PHP_EOL."----------------------MORPHOLOGY----------------------".PHP_EOL, FILE_APPEND);
        $sentenceString = "";
        foreach ($morphology as $sentence => $words) {
            $sentenceString .= $sentence . " => " ;
            foreach ($words as $word){
                $sentenceString .= "[" . implode(";", $word). "] ";
            }
            $sentenceString .= PHP_EOL;
        }
        file_put_contents("output.txt", $sentenceString, FILE_APPEND);

        file_put_contents("output.txt", PHP_EOL."----------------------SYNTAX----------------------".PHP_EOL, FILE_APPEND);
        $sentenceString = "";
        foreach ($syntax as $sentence => $groups) {
            $sentenceString .= $sentence . " => " .implode(PHP_EOL . "     ", $groups);
            $sentenceString .= PHP_EOL;
        }
        file_put_contents("output.txt", $sentenceString, FILE_APPEND);

        file_put_contents("output.txt", PHP_EOL."----------------------SYNTAX XML----------------------".PHP_EOL, FILE_APPEND);
        $sentenceString = "";
        foreach ($syntaxXML as $sentence => $groups) {
            $sentenceString .= $sentence . " => " .implode(PHP_EOL . "     ", $groups);
            $sentenceString .= PHP_EOL;
        }
        file_put_contents("output.txt", $sentenceString, FILE_APPEND);

        return $keywords;
    }
}