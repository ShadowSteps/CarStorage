<?php


namespace Shadows\CarStorage\NLP\NLP\Keywords;


use Shadows\CarStorage\NLP\NLP\Syntax\Group\SyntaxGroup;

class KeywordsMatcher
{
    private $schemes = [];

    public function addScheme(string $scheme) {
        $this->schemes[] = $scheme;
    }

    public function extractKeywordsFromGroup(SyntaxGroup $group): array
    {
        $keywordArray = [];
        $groupText = $group->__toString();
        echo $groupText . PHP_EOL;
        foreach ($this->schemes as $scheme)
        {
            if (preg_match("/$scheme/", $groupText, $matches))
            {
                $keywords = preg_replace('/[A-Z\[\]]/', "", $matches[3]);
                $keywords = trim(preg_replace('/\s+/', " ", $keywords));
                $keywordArray[] = $keywords;
            }
        }
        return $keywordArray;
    }
}