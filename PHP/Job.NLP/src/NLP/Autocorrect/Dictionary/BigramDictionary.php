<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 21.1.2017 г.
 * Time: 9:29
 */

namespace Shadows\CarStorage\NLP\NLP\Autocorrect\Dictionary;


use Shadows\CarStorage\NLP\NLP\Morphology\Word\Word;

class BigramDictionary
{
    private $dictionary = [];

    private function getStringBigrams(string $word): array {
        $bigrams = [];
        $length = mb_strlen($word);
        for ($i = 0; $i < $length - 1; $i++) {
            $bigrams[] = mb_substr($word, $i, 2);
        }
        return $bigrams;
    }

    private function getWordBigrams(Word $word) : array  {
        $wordString = $word->getRawForm();
        return $this->getStringBigrams($wordString);
    }

    public function addWordToDictionary(Word $word) {
        $bigrams = $this->getWordBigrams($word);
        foreach ($bigrams as $bigram) {
            if (!isset($this->dictionary[$bigram]))
                $this->dictionary[$bigram] = [];
            $this->dictionary[$bigram][] = $word->getRawForm();
        }
    }

    public function getBigramArrayForString(string $word): array {
        $wordBigrams = $this->getStringBigrams($word);
        $result = [];
        foreach ($wordBigrams as $bigram){
            if (!isset($this->dictionary[$bigram]))
                continue;
            $result[$bigram] = $this->dictionary[$bigram];
        }
        return $result;
    }
}