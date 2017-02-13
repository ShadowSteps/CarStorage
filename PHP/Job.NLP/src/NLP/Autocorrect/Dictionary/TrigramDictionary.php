<?php

namespace Shadows\CarStorage\NLP\NLP\Autocorrect\Dictionary;


use Shadows\CarStorage\NLP\NLP\Morphology\Word\Word;

class TrigramDictionary
{
    private $dictionary = [];

    private function getStringTrigrams(string $word): array {
        $trigrams = [];
        $length = mb_strlen($word);
        for ($i = 0; $i < $length - 1; $i++) {
            $trigrams[] = mb_substr($word, $i, 2);
        }
        return $trigrams;
    }

    private function getWordTrigrams(Word $word) : array  {
        $wordString = $word->getRawForm();
        return $this->getStringTrigrams($wordString);
    }

    public function addWordToDictionary(Word $word) {
        $trigrams = $this->getWordTrigrams($word);
        foreach ($trigrams as $trigram) {
            if (!isset($this->dictionary[$trigram]))
                $this->dictionary[$trigram] = [];
            $this->dictionary[$trigram][] = $word->getRawForm();
        }
    }

    public function getTrigramArrayForString(string $word): array {
        $trigrams = $this->getStringTrigrams($word);
        $result = [];
        foreach ($trigrams as $trigram){
            if (!isset($this->dictionary[$trigram]))
                continue;
            $result[$trigram] = $this->dictionary[$trigram];
        }
        return $result;
    }
}