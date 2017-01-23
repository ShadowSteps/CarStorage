<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 21.1.2017 г.
 * Time: 9:57
 */

namespace Shadows\CarStorage\NLP\NLP\Autocorrect;


use Shadows\CarStorage\NLP\NLP\Autocorrect\Dictionary\BigramDictionary;

class StringAutoCorrect
{
    private $bigramDictionary;

    /**
     * StringAutoCorrect constructor.
     * @param BigramDictionary $bigramDictionary
     */
    public function __construct(BigramDictionary $bigramDictionary)
    {
        $this->bigramDictionary = $bigramDictionary;
    }

    public function findClosest(string $word, int $topInterval = 10, float $threshold = 0.85): string {
        $bigrams = $this->bigramDictionary->getBigramArrayForString($word);
        $words = [];
        foreach ($bigrams as $bigram) {
            foreach ($bigram as $strword) {
                if (isset($words[$strword]))
                    $words[$strword]++;
                else
                    $words[$strword] = 1;
            }
        }
        arsort($words);
        $i = 0;
        $topword = $word;
        $topSimilarity = 0;
        $length = mb_strlen($word);
        foreach ($words as $key => $next){
            $i++;
            if ($i > $topInterval)
                break;
            $similarity = levenshtein($next, $word);
            if ($similarity > $topSimilarity && $similarity < (1 - $threshold)){
                $topword = $key;
                $topSimilarity = $similarity;
            }
        }
        return $topword;
    }

}