<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 21.1.2017 г.
 * Time: 9:57
 */

namespace Shadows\CarStorage\NLP\NLP\Autocorrect;


use Shadows\CarStorage\NLP\NLP\Autocorrect\Dictionary\TrigramDictionary;

class StringAutoCorrect
{
    private $bigramDictionary;

    /**
     * StringAutoCorrect constructor.
     * @param TrigramDictionary $bigramDictionary
     */
    public function __construct(TrigramDictionary $bigramDictionary)
    {
        $this->bigramDictionary = $bigramDictionary;
    }

    public function findClosest(string $word, int $topInterval = 10, float $threshold = 0.85): string {
        $trigrams = $this->bigramDictionary->getTrigramArrayForString($word);
        $words = [];
        foreach ($trigrams as $trigram) {
            foreach ($trigram as $strword) {
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
            $similarity = 1 / (1 + abs($length - ($next + 1)));
            if ($similarity > $topSimilarity && $similarity > $threshold){
                $topword = $key;
                $topSimilarity = $similarity;
            }
        }
        return $topword;
    }

}