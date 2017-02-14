<?php

namespace Shadows\CarStorage\NLP\NLP\Syntax;

use Shadows\CarStorage\NLP\NLP\Morphology\Word\Word;
use Shadows\CarStorage\NLP\NLP\Morphology\Word\WordType;
use Shadows\CarStorage\NLP\NLP\Syntax\Exception\NoMoreElementsInRuleException;
use Shadows\CarStorage\NLP\NLP\Syntax\Group\SyntaxGroup;
use Shadows\CarStorage\NLP\NLP\Syntax\Group\SyntaxGroupType;

class SyntaxAnalyzer
{
    private $grammarRules = [];
    private $ruleIndex = [];

    public function addRule(SyntaxRule $rule) {
        $this->grammarRules[] = $rule;
        for ($i = 0; $i < $rule->getElementCount(); $i++){
            $item = $rule->getAtPosition($i);
            $this->ruleIndex[$i][$item][] = $rule;
            if ($item[2] == "E")
                $this->ruleIndex[$i]["{SE:".WordType::Unrecognized."}"][] = $rule;
        }
    }

    public function analyze(array $words, float $threshold = 0.80): array {
        $possibilitiesCache = [];
        $startPoint = 0;
        $endPoint = count($words);
        $thresholdPoint = floor(($endPoint - $startPoint) * (1 - $threshold));
        $thresholdPoint = $thresholdPoint >= 0 ? $thresholdPoint : 0;
        $fullMatches = [];
        foreach ($words as $key => $word) {
            foreach ($word as $meaning)
            {
                /**
                 * @var $meaning Word
                 */
                $item = $meaning->getWordTypeString();
                $newFullRules = [];
                if (isset($this->ruleIndex[0][$item])) {
                    $newRules = $this->ruleIndex[0][$item];
                    foreach ($newRules as $rule) {
                        /**
                         * @var $rule SyntaxRule
                         */
                        $newGroup = new SyntaxGroup($rule->getType());
                        $newGroup->addChild($meaning);
                        $match = new SyntaxMatch($key, $rule, $newGroup);
                        if ($match->getRule()->getElementCount() == 1)
                            $newFullRules[] = $match;
                        else
                            $possibilitiesCache[$match->getPositionTo()][$match->getNextElement()][] = $match;
                    }
                }
                if (isset($possibilitiesCache[$key][$item])) {
                    $newRules = $possibilitiesCache[$key][$item];
                    foreach ($newRules as $rule) {
                        /**
                         * @var $rule SyntaxMatch
                         */
                        $newGroup = $rule->getGroup()->cloneGroup();
                        $newGroup->addChild($meaning);
                        $match = new SyntaxMatch($rule->getPositionFrom(), $rule->getRule(), $newGroup);
                        if ($match->isFinished())
                            array_push($newFullRules, $match);
                        else
                            $possibilitiesCache[$match->getPositionTo()][$match->getNextElement()][] = $match;
                    }
                }
                while (count($newFullRules) > 0) {
                    /**
                     * @var $newRule SyntaxMatch
                     */
                    $newRule = array_shift($newFullRules);
                    if ($newRule->getPositionFrom() <= ($startPoint + $thresholdPoint) &&
                        $newRule->getPositionTo() >= $endPoint - $thresholdPoint &&
                        $newRule->getGroup()->getType() == SyntaxGroupType::Sentence
                    ) {
                        $fullMatches[] = $newRule->getGroup();
                    } else {
                        if (isset($this->ruleIndex[0][$newRule->getRule()->getTypeString()])) {
                            $newRules = $this->ruleIndex[0][$newRule->getRule()->getTypeString()];
                            foreach ($newRules as $rule) {
                                /**
                                 * @var $rule SyntaxRule
                                 */
                                $newGroup = new SyntaxGroup($rule->getType());
                                $newGroup->addChild($newRule->getGroup()->cloneGroup());
                                $match = new SyntaxMatch($newRule->getPositionFrom(), $rule, $newGroup);
                                if ($match->getRule()->getElementCount() == 1)
                                    $newFullRules[] = $match;
                                else
                                    $possibilitiesCache[$match->getPositionTo()][$match->getNextElement()][] = $match;
                            }
                        }
                        if (isset($possibilitiesCache[$newRule->getPositionFrom()][$newRule->getRule()->getTypeString()])) {
                            foreach ($possibilitiesCache[$newRule->getPositionFrom()][$newRule->getRule()->getTypeString()] as $possibility) {
                                /**
                                 * @var $possibility SyntaxMatch
                                 */
                                $group = $possibility->getGroup()->cloneGroup();
                                $group->addChild($newRule->getGroup()->cloneGroup());
                                $match = new SyntaxMatch($possibility->getPositionFrom(), $possibility->getRule(), $group);
                                if ($match->isFinished())
                                    array_push($newFullRules, $match);
                                else
                                    $possibilitiesCache[$match->getPositionTo()][$match->getNextElement()][] = $match;
                            }
                        }
                    }
                }
            }
        }
        $currentMinimum = 0;
        $results = [];
        foreach ($fullMatches as $match) {
            /**
             * @var $match SyntaxGroup
             */
            $matchCount = $match->getChildCount();
            if ($matchCount >= $currentMinimum) {
                $results[] = $match;
                usort($results, function ($val, $val2){
                    $a = $val->getChildCount();
                    $b = $val2->getChildCount();
                    if ($a == $b) {
                        return 0;
                    }
                    return ($a > $b) ? -1 : 1;
                });
                if (count($results) > 5) {
                    array_pop($results);
                    $currentMinimum = $results[4]->getChildCount();
                }
            }
        }
        return $results;
    }
}