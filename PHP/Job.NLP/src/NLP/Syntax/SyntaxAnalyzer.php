<?php

namespace Shadows\CarStorage\NLP\NLP\Syntax;

use Shadows\CarStorage\NLP\NLP\Morphology\Word\Word;
use Shadows\CarStorage\NLP\NLP\Morphology\Word\WordType;
use Shadows\CarStorage\NLP\NLP\Syntax\Exception\NoMoreElementsInRuleException;
use Shadows\CarStorage\NLP\NLP\Syntax\Group\SyntaxGroup;
use Shadows\CarStorage\NLP\NLP\Syntax\Group\SyntaxGroupType;

class SyntaxAnalyzer
{
    /**
     * @var SyntaxRule[]
     */
    private $grammarRules = [];
    /**
     * @var SyntaxRule[]
     */
    private $indexedRules = [];
    public function addRule(SyntaxRule $rule) {
        $this->grammarRules[] = $rule;
        $this->indexedRules[$rule->getType()][] = $rule;
    }

    private function matchFinishProcedure(SyntaxMatch $match, array &$possibilities): array {
        $completeSets = [];
        $completeSets[] = $match;
        foreach ($possibilities as $key => $possibility) {
            /**
             * @var $possibility SyntaxMatch
             */
            if ($possibility->isFinished())
                continue;
            $element = $possibility->getRule()->getCurrentElement();
            $matches = [];
            if (!(preg_match("/\\{SG:([A-Z]+)\\}/", $element, $matches)&&SyntaxGroupType::isSyntaxGroup($matches[1])))
                continue;
            if ($matches[1] != $match->getGroup()->getType())
                continue;
            if ($possibility->getPositionTo() != $match->getPositionFrom())
                continue;
            $newPossibility = new SyntaxMatch(
                $possibility->getPositionFrom(),
                clone $possibility->getRule(),
                $possibility->getGroup()->cloneGroup());
            $newPossibility->getGroup()->addChild($match->getGroup()->cloneGroup());
            $possibilities[] = $newPossibility;
            try {
                $newPossibility->getRule()->moveToNextElement();
            }
            catch (NoMoreElementsInRuleException $exp) {
                $completeSets = array_merge($completeSets, $this->matchFinishProcedure($newPossibility, $possibilities));
            }
        }
        return $completeSets;
    }

    /**
     * @param array $words
     * @param float $threshold
     * @return SyntaxGroup
     */
    public function analyze(array $words, float $threshold = 0.80): array {
        $possibilities = [];
        $oldLength = 0;
        $matches = [];
        foreach ($words as $key => $word) {
            foreach ($this->grammarRules as $schedulerRule) {
                /**
                 * @var $rule SyntaxRule
                 */
                $schedulerRule->resetPosition();
                $schedulerRule->moveToNextElement();
                $element = $schedulerRule->getCurrentElement();
                $match = [];
                if (!(preg_match("/\\{SE:([A-Za-z]+)\\}/", $element, $match)&&WordType::isValidType($match[1])))
                    continue;
                foreach ($word as $meaning) {
                    /**
                     * @var $meaning Word
                     */
                    if ($match[1] != $meaning->getWordType() && $meaning->getWordType() != WordType::Unrecognized)
                        continue;
                    $rule = clone $schedulerRule;
                    $group = new SyntaxGroup($rule->getType());
                    $group->addChild($meaning);
                    $possibility = new SyntaxMatch($key, $rule, $group);
                    $matchString = $possibility->getRule()->__toString()."->".$possibility->getGroup()->__toString();
                    $matchHASH = hash("sha256", $matchString);
                    if (isset($matches[$matchHASH]))
                        continue;
                    $possibilities[] = $possibility;
                    if (!$possibility->isFinished())
                        $possibility->getRule()->moveToNextElement();
                    $matches[$matchHASH] = true;
                }
            }
        }
        while ($oldLength != count($possibilities)) {
            $oldLength = count($possibilities);
            foreach ($possibilities as $key => $possibility) {
                /**
                 * @var $possibility SyntaxMatch
                 */
                if ($possibility->isFinished()) {
                    foreach ($this->grammarRules as $schedulerRule) {
                        /**
                         * @var $rule SyntaxRule
                         */
                        $schedulerRule->resetPosition();
                        $schedulerRule->moveToNextElement();
                        $element = $schedulerRule->getCurrentElement();
                        $match = [];
                        if (preg_match("/\\{SG:([A-Z]+)\\}/", $element, $match)&&SyntaxGroupType::isSyntaxGroup($match[1])) {
                            if ($match[1] != $possibility->getGroup()->getType())
                                continue;
                            $rule = clone $schedulerRule;
                            $group = new SyntaxGroup($rule->getType());
                            $group->addChild($possibility->getGroup()->cloneGroup());
                            $newPossibility = new SyntaxMatch($possibility->getPositionFrom(), $rule, $group);
                            $matchString = $newPossibility->getRule()->__toString()."->".$newPossibility->getGroup()->__toString();
                            $matchHASH = hash("sha256", $matchString);
                            if (isset($matches[$matchHASH]))
                                continue;
                            if (!$newPossibility->isFinished())
                                $newPossibility->getRule()->moveToNextElement();
                            $possibilities[] = $newPossibility;
                            $matches[$matchHASH] = true;
                        }
                    }
                } else {
                    $element = $possibility->getRule()->getCurrentElement();
                    $matchStrings = [];
                    if (preg_match("/\\{SE:([A-Z]+)\\}/", $element, $matchStrings)&&WordType::isValidType($matchStrings[1])){
                        $word = $words[$possibility->getPositionTo()];
                        foreach ($word as $meaning) {
                            /**
                             * @var $meaning Word
                             */
                            if ($matchStrings[1] != $meaning->getWordType() && $meaning->getWordType() != WordType::Unrecognized)
                                continue;
                            $rule = clone $possibility->getRule();
                            $newPossibility = new SyntaxMatch($possibility->getPositionFrom(), $rule, $possibility->getGroup()->cloneGroup());
                            $newPossibility->getGroup()->addChild($meaning);
                            $matchString = $newPossibility->getRule()->__toString()."->".$newPossibility->getGroup()->__toString();
                            $matchHASH = hash("sha256", $matchString);
                            if (isset($matches[$matchHASH]))
                                continue;
                            $possibilities[] = $newPossibility;
                            if (!$newPossibility->isFinished())
                                $newPossibility->getRule()->moveToNextElement();
                            $matches[$matchHASH] = true;
                        }
                    } else if (preg_match("/\\{SG:([A-Z]+)\\}/", $element, $matchStrings)&&SyntaxGroupType::isSyntaxGroup($matchStrings[1])) {
                        foreach ($possibilities as $mkey => $match) {
                            /**
                             * @var $match SyntaxMatch
                             */
                            if (!$match->isFinished())
                                continue;
                            if ($possibility->getPositionTo() != $match->getPositionFrom())
                                continue;
                            if ($matchStrings[1] != $match->getGroup()->getType())
                                continue;
                            $newPossibility = new SyntaxMatch($possibility->getPositionFrom(), clone $possibility->getRule(), $possibility->getGroup()->cloneGroup());
                            $newPossibility->getGroup()->addChild($match->getGroup()->cloneGroup());
                            $matchString = $newPossibility->getRule()->__toString()."->".$newPossibility->getGroup()->__toString();
                            $matchHASH = hash("sha256", $matchString);
                            if (isset($matches[$matchHASH]))
                                continue;
                            if (!$newPossibility->isFinished())
                                $newPossibility->getRule()->moveToNextElement();
                            $possibilities[] = $newPossibility;
                            $matches[$matchHASH] = true;
                        }
                    }
                }
            }
        }
        $results = [];
        $length = count($words);
        foreach ($possibilities as $possibility) {
            echo $possibility->getGroup() . " -> " . $possibility->getRule() . PHP_EOL;
            $similarity = 1 / (1 + abs($possibility->getGroup()->getChildCount() - $length));
            if ($similarity > $threshold&&$possibility->isFinished())
                $results[] = $possibility->getGroup();
        }
        return $results;
    }
}