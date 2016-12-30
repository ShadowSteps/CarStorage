<?php

namespace Shadows\CarStorage\NLP\NLP\Morphology;

use Shadows\CarStorage\NLP\NLP\Morphology\Word\Word;
use Shadows\CarStorage\NLP\NLP\Morphology\Word\WordType;

/**
 * Created by PhpStorm.
 * User: John
 * Date: 30.12.2016 г.
 * Time: 18:01
 */
class Dictionary
{
    /**
     * @var Word[]
     */
    private $wordArray = [];

    public function addNewWord(Word $word) {
        $wordHash = hash("sha256", $word->getRawForm());
        $this->wordArray[$wordHash] = $word;
    }

    public function __construct(array $words = [])
    {
        foreach ($words as $word)
            $this->addNewWord($word);
    }

    public function findWord(string $word): Word {
        $hash = hash("sha256", $word);
        if (array_key_exists($hash, $this->wordArray))
            return $this->wordArray[$hash];
        else
            return new Word($word, $word, WordType::Unrecognized);
    }
}