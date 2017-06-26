<?php

namespace Shadows\CarStorage\NLP\NLP\Morphology;

use Shadows\CarStorage\NLP\NLP\Morphology\Word\Word;

/**
 * Created by PhpStorm.
 * User: John
 * Date: 30.12.2016 г.
 * Time: 18:01
 */
class DictionaryReader
{
    public static function ReadDictionaryFromFile(string $filePath): Dictionary {
        if (!file_exists($filePath))
            throw new \InvalidArgumentException("File dictionary not found on path: ".$filePath);
        $dictionary = new Dictionary();
        $file = fopen($filePath, "r");
        while (($line = fgets($file)) !== false) {
            $matches = [];
            $result = preg_match("/(.*?),(.*?)\\.([A-Z]+)/", $line, $matches);
            if ($result)
                $dictionary->addNewWord(
                    new Word($matches[1], $matches[2], $matches[3])
                );
        }
        fclose($file);
        return $dictionary;
    }
}